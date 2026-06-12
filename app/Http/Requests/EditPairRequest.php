<?php

namespace App\Http\Requests;

use SanderMuller\FluentValidation\FluentFormRequest;
use SanderMuller\FluentValidation\FluentRule;

class EditPairRequest extends FluentFormRequest
{
    public function rules(): array
    {
        return [
            'pair'     => FluentRule::string('Symbol')->required()->min(3)->max(20),
            'side'     => FluentRule::string('Side')->required()->in(['buy', 'sell']),
            'lavarage' => FluentRule::integer('Leverage')->required()->min(1)->max(125),
            'margin'   => FluentRule::integer('Margin')->required()->min(1),
        ];
    }
}
