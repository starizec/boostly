<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Models\Company;
use App\Models\Country;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPhoneNumberFormComponent(),
                        $this->getCompanyNameFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getPhoneNumberFormComponent(): Component
    {
        return TextInput::make('phone_number')
            ->label('Phone Number')
            ->tel()
            ->maxLength(255);
    }

    protected function getCompanyNameFormComponent(): Component
    {
        return TextInput::make('company_name')
            ->label('Company Name')
            ->required()
            ->maxLength(255);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeRegister(array $data): array
    {
        $country = Country::firstOrCreate(
            ['code' => 'XX'],
            ['name' => 'Unknown'],
        );

        $company = Company::create([
            'name' => $data['company_name'],
            'vat_number' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
            'country_id' => $country->id,
        ]);

        unset($data['company_name']);

        $data['company_id'] = $company->id;
        $data['role'] = 'user';

        return $data;
    }
}
