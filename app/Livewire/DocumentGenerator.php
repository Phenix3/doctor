<?php

namespace App\Livewire;

use App\Data\DocumentGenerationRequest;
use App\Data\DocumentGenerationResult;
use App\Enums\Country;
use App\Enums\DocumentType;
use App\Enums\ToneLevel;
use App\Http\Requests\DocumentRequest;
use App\Services\DocumentGeneratorService;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DocumentGenerator extends Component
{
    // Form fields
    #[Validate('required|string')]
    public string $country = '';
    
    #[Validate('required|string')]
    public string $document_type = '';
    
    #[Validate('required|string')]
    public string $tone_level = '';
    
    #[Validate('required|string|in:fr,en')]
    public string $language = 'fr';
    
    public array $fields = [];
    
    // UI state
    public bool $showPreview = false;
    public bool $isGenerating = false;
    public string $generatedContent = '';
    public string $previewContent = '';
    public array $validationErrors = [];
    public array $requiredFields = [];
    public bool $showFieldsForm = false;
    
    // Document generation result
    public ?DocumentGenerationResult $documentResult = null;

    protected $listeners = [
        'countryChanged' => 'handleCountryChange',
        'documentTypeChanged' => 'handleDocumentTypeChange',
        'toneChanged' => 'handleToneChange',
        'languageChanged' => 'handleLanguageChange',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->country = '';
        $this->document_type = '';
        $this->tone_level = '';
        $this->language = 'fr';
        $this->fields = [];
        $this->showPreview = false;
        $this->isGenerating = false;
        $this->generatedContent = '';
        $this->previewContent = '';
        $this->validationErrors = [];
        $this->requiredFields = [];
        $this->showFieldsForm = false;
        $this->documentResult = null;
    }

    public function handleCountryChange($countryKey)
    {
        $this->country = $countryKey;
        $this->updateRequiredFields();
        $this->validateStep();
    }

    public function handleDocumentTypeChange($documentTypeKey)
    {
        $this->document_type = $documentTypeKey;
        $this->updateRequiredFields();
        $this->validateStep();
    }

    public function handleToneChange($toneLevelKey)
    {
        $this->tone_level = $toneLevelKey;
        $this->validateStep();
    }

    public function handleLanguageChange($languageCode)
    {
        $this->language = $languageCode;
        $this->validateStep();
    }

    public function updateRequiredFields()
    {
        if ($this->document_type) {
            $this->requiredFields = DocumentRequest::getRequiredFieldsForDocumentType(
                strtoupper($this->document_type)
            );
            
            // Initialize fields array with empty values for required fields
            foreach ($this->requiredFields as $field) {
                if (!isset($this->fields[$field])) {
                    $this->fields[$field] = '';
                }
            }
            
            $this->showFieldsForm = !empty($this->requiredFields);
        } else {
            $this->requiredFields = [];
            $this->showFieldsForm = false;
        }
    }

    public function validateStep()
    {
        $this->validationErrors = [];
        
        try {
            $this->validate([
                'country' => 'required|string',
                'document_type' => 'required|string',
                'tone_level' => 'required|string',
                'language' => 'required|string|in:fr,en',
            ]);
        } catch (ValidationException $e) {
            $this->validationErrors = $e->validator->errors()->toArray();
        }
    }

    public function validateFields()
    {
        $this->validationErrors = [];
        
        // Validate required fields are filled
        foreach ($this->requiredFields as $field) {
            if (empty($this->fields[$field] ?? '')) {
                $this->validationErrors["fields.{$field}"] = ["Ce champ est obligatoire."];
            }
        }
        
        return empty($this->validationErrors);
    }

    public function generatePreview()
    {
        if (!$this->validateFields()) {
            return;
        }

        $this->isGenerating = true;
        $this->previewContent = '';

        try {
            $request = $this->createDocumentRequest();
            $service = app(DocumentGeneratorService::class);
            
            $this->previewContent = $service->previewDocument($request);
            $this->showPreview = true;
            
        } catch (\Exception $e) {
            Log::error('Document preview generation failed', [
                'error' => $e->getMessage(),
                'request_data' => $this->getRequestData()
            ]);
            
            $this->addError('preview', 'Erreur lors de la génération de l\'aperçu: ' . $e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    public function generateDocument()
    {
        if (!$this->validateFields()) {
            return;
        }

        $this->isGenerating = true;
        $this->generatedContent = '';

        try {
            $request = $this->createDocumentRequest();
            $service = app(DocumentGeneratorService::class);
            
            $this->documentResult = $service->generateDocument($request);
            $this->generatedContent = $this->documentResult->content;
            
            $this->dispatch('document-generated', [
                'content' => $this->generatedContent,
                'metadata' => $this->documentResult->metadata
            ]);
            
        } catch (\Exception $e) {
            Log::error('Document generation failed', [
                'error' => $e->getMessage(),
                'request_data' => $this->getRequestData()
            ]);
            
            $this->addError('generation', 'Erreur lors de la génération du document: ' . $e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    public function downloadDocument()
    {
        if (!$this->documentResult) {
            $this->addError('download', 'Aucun document généré à télécharger.');
            return;
        }

        try {
            $service = app(DocumentGeneratorService::class);
            $pdfPath = $service->generatePDF($this->documentResult);
            
            return response()->download($pdfPath, $this->getDocumentFilename() . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('Document download failed', [
                'error' => $e->getMessage(),
                'document_id' => $this->documentResult->id ?? null
            ]);
            
            $this->addError('download', 'Erreur lors du téléchargement: ' . $e->getMessage());
        }
    }

    private function createDocumentRequest(): DocumentGenerationRequest
    {
        return new DocumentGenerationRequest(
            country: $this->country,
            document_type: $this->document_type,
            tone_level: $this->tone_level,
            language: $this->language,
            fields: $this->fields,
            session_id: session()->getId()
        );
    }

    private function getRequestData(): array
    {
        return [
            'country' => $this->country,
            'document_type' => $this->document_type,
            'tone_level' => $this->tone_level,
            'language' => $this->language,
            'fields_count' => count($this->fields),
            'required_fields_count' => count($this->requiredFields)
        ];
    }

    private function getDocumentFilename(): string
    {
        $documentTypeLabel = DocumentType::make($this->document_type)->label ?? 'document';
        $countryLabel = Country::make($this->country)->label ?? 'document';
        
        return strtolower(str_replace([' ', '\''], ['_', ''], $documentTypeLabel)) . '_' . 
               strtolower(str_replace([' ', '\''], ['_', ''], $countryLabel)) . '_' . 
               date('Y-m-d');
    }

    // Computed properties for the view
    public function getCountriesProperty()
    {
        return collect(Country::toArray())->map(function ($value, $key) {
            return [
                'key' => $key,
                'value' => $value,
                'label' => Country::make($key)->label
            ];
        });
    }

    public function getDocumentTypesProperty()
    {
        return collect(DocumentType::toArray())->map(function ($value, $key) {
            return [
                'key' => $key,
                'value' => $value,
                'label' => DocumentType::make($key)->label
            ];
        });
    }

    public function getToneLevelsProperty()
    {
        return collect(ToneLevel::toArray())->map(function ($value, $key) {
            return [
                'key' => $key,
                'value' => $value,
                'label' => ToneLevel::make($key)->label
            ];
        });
    }

    public function getLanguagesProperty()
    {
        return [
            ['key' => 'fr', 'value' => 'fr', 'label' => 'Français'],
            ['key' => 'en', 'value' => 'en', 'label' => 'English'],
        ];
    }

    public function getFieldLabelsProperty()
    {
        return [
            'applicant_name' => 'Nom du demandeur',
            'applicant_address' => 'Adresse du demandeur',
            'applicant_phone' => 'Téléphone du demandeur',
            'applicant_email' => 'Email du demandeur',
            'recipient_title' => 'Titre du destinataire',
            'recipient_name' => 'Nom du destinataire',
            'recipient_organization' => 'Organisation du destinataire',
            'subject' => 'Objet',
            'request_details' => 'Détails de la demande',
            'position_title' => 'Titre du poste',
            'motivation_content' => 'Contenu de la motivation',
            'qualifications' => 'Qualifications',
            'student_name' => 'Nom de l\'étudiant',
            'student_address' => 'Adresse de l\'étudiant',
            'institution_name' => 'Nom de l\'institution',
            'study_program' => 'Programme d\'études',
            'internship_duration' => 'Durée du stage',
            'internship_period' => 'Période du stage',
            'supervisor_name' => 'Nom du superviseur',
            'declarant_name' => 'Nom du déclarant',
            'declarant_address' => 'Adresse du déclarant',
            'declaration_content' => 'Contenu de la déclaration',
            'identity_document_type' => 'Type de pièce d\'identité',
            'identity_document_number' => 'Numéro de pièce d\'identité',
            'complainant_name' => 'Nom du plaignant',
            'complainant_address' => 'Adresse du plaignant',
            'complaint_subject' => 'Objet de la plainte',
            'complaint_details' => 'Détails de la plainte',
            'incident_date' => 'Date de l\'incident',
            'desired_resolution' => 'Résolution souhaitée',
            'requester_name' => 'Nom du demandeur',
            'requester_address' => 'Adresse du demandeur',
            'act_type' => 'Type d\'acte',
            'justification' => 'Justification',
            'supporting_documents' => 'Documents justificatifs',
            'appellant_name' => 'Nom de l\'appelant',
            'appellant_address' => 'Adresse de l\'appelant',
            'original_decision_reference' => 'Référence de la décision originale',
            'appeal_grounds' => 'Motifs du recours',
            'requested_action' => 'Action demandée',
            'original_decision_date' => 'Date de la décision originale',
            'sender_name' => 'Nom de l\'expéditeur',
            'sender_address' => 'Adresse de l\'expéditeur',
            'message_content' => 'Contenu du message',
            'date' => 'Date',
        ];
    }

    public function render()
    {
        return view('livewire.document-generator');
    }
}