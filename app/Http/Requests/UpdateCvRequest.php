<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\CvDefaults;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `content` est stocke en JSON, il n'y a donc aucun schema impose par la base :
 * cette classe est le seul endroit qui garantit sa forme. Les bornes de
 * longueur protegent aussi le service, ouvert sans compte, du stockage abusif.
 */
class UpdateCvRequest extends FormRequest
{
    /** L'autorisation est faite en amont par le middleware `cv.token`. */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hex = 'regex:/^#[0-9a-fA-F]{6}$/';

        return [
            'template' => ['required', 'string', Rule::in(CvDefaults::TEMPLATES)],

            'theme' => ['required', 'array:primary,accent'],
            'theme.primary' => ['required', 'string', $hex],
            'theme.accent' => ['required', 'string', $hex],

            'fonts' => ['required', 'array:title,body'],
            'fonts.title' => ['required', 'string', Rule::in(CvDefaults::FONTS)],
            'fonts.body' => ['required', 'string', Rule::in(CvDefaults::FONTS)],

            'is_public' => ['required', 'boolean'],
            'allow_indexing' => ['required', 'boolean'],

            'content' => ['required', 'array'],
            'content.schema_version' => ['required', 'integer', 'in:'.CvDefaults::SCHEMA_VERSION],

            'content.identity' => ['required', 'array:fullName,jobTitle,techLine,tagline'],
            'content.identity.fullName' => ['present', 'string', 'max:120'],
            'content.identity.jobTitle' => ['present', 'string', 'max:160'],
            'content.identity.techLine' => ['present', 'string', 'max:240'],
            'content.identity.tagline' => ['present', 'string', 'max:600'],

            'content.profile' => ['present', 'string', 'max:2000'],

            'content.contact' => ['present', 'array', 'max:'.CvDefaults::MAX_CONTACTS],
            'content.contact.*.type' => ['required', 'string', Rule::in(CvDefaults::CONTACT_TYPES)],
            'content.contact.*.value' => ['present', 'string', 'max:200'],

            'content.sections' => ['present', 'array', 'max:'.CvDefaults::MAX_SECTIONS],
            'content.sections.*.id' => ['required', 'string', 'max:60'],
            'content.sections.*.type' => ['required', 'string', Rule::in(CvDefaults::SECTION_TYPES)],
            'content.sections.*.title' => ['required', 'string', 'max:80'],
            'content.sections.*.column' => ['required', 'string', Rule::in(CvDefaults::COLUMNS)],
            'content.sections.*.enabled' => ['required', 'boolean'],
            'content.sections.*.items' => ['present', 'array', 'max:'.CvDefaults::MAX_ITEMS_PER_SECTION],
        ];
    }

    /**
     * Les items n'ont pas la meme forme selon le type de section, ce que la
     * syntaxe `sections.*.items.*` ne sait pas exprimer. On valide donc chaque
     * section avec le jeu de regles correspondant a son type.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach ((array) $this->input('content.sections', []) as $index => $section) {
                    $rules = $this->itemRulesFor($section['type'] ?? '');

                    if ($rules === []) {
                        continue;
                    }

                    // Le validateur imbrique est alimente avec une structure
                    // plate (`items`), car une cle contenant des points serait
                    // lue comme un chemin et les regles ne trouveraient rien.
                    $scoped = [];

                    foreach ($rules as $suffix => $rule) {
                        $scoped['items'.$suffix] = $rule;
                    }

                    $nested = validator(['items' => $section['items'] ?? []], $scoped, [], $this->itemAttributes());

                    foreach ($nested->errors()->messages() as $field => $messages) {
                        foreach ($messages as $message) {
                            $validator->errors()->add("content.sections.{$index}.{$field}", $message);
                        }
                    }
                }
            },
        ];
    }

    /** @return array<string, array<int, string>> */
    private function itemRulesFor(string $type): array
    {
        return match ($type) {
            'experiences' => [
                '.*.period' => ['present', 'string', 'max:60'],
                '.*.role' => ['present', 'string', 'max:140'],
                '.*.company' => ['present', 'string', 'max:120'],
                '.*.location' => ['present', 'string', 'max:120'],
                '.*.bullets' => ['present', 'array', 'max:'.CvDefaults::MAX_BULLETS_PER_ITEM],
                '.*.bullets.*' => ['string', 'max:400'],
            ],
            'education', 'certifications' => [
                '.*.period' => ['present', 'string', 'max:60'],
                '.*.degree' => ['present', 'string', 'max:200'],
                '.*.school' => ['present', 'string', 'max:140'],
                '.*.location' => ['present', 'string', 'max:120'],
                '.*.detail' => ['present', 'string', 'max:240'],
            ],
            'skills' => [
                '.*.label' => ['present', 'string', 'max:200'],
                '.*.level' => ['required', 'integer', 'between:0,100'],
            ],
            'languages' => [
                '.*.label' => ['present', 'string', 'max:80'],
                '.*.mention' => ['present', 'string', 'max:80'],
                '.*.level' => ['required', 'integer', 'between:0,5'],
            ],
            'tools', 'interests' => [
                '.*' => ['string', 'max:160'],
            ],
            default => [],
        };
    }

    /** @return array<string, string> */
    private function itemAttributes(): array
    {
        return [
            'period' => 'période',
            'role' => 'poste',
            'company' => 'entreprise',
            'location' => 'lieu',
            'bullets' => 'missions',
            'degree' => 'intitulé',
            'school' => 'établissement',
            'label' => 'libellé',
            'level' => 'niveau',
        ];
    }
}
