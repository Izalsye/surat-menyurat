<?php

namespace App\Enum;

use Illuminate\Support\Facades\Gate;

enum Permission: string
{
    case ViewUser = 'view_user';
    case AddUser = 'add_user';
    case EditUser = 'edit_user';
    case DeleteUser = 'delete_user';
    case ViewRole = 'view_role';
    case AddRole = 'add_role';
    case EditRole = 'edit_role';
    case DeleteRole = 'delete_role';
    case ViewIncomingLetter = 'view_incoming_letter';
    case AddIncomingLetter = 'add_incoming_letter';
    case EditIncomingLetter = 'edit_incoming_letter';
    case DeleteIncomingLetter = 'delete_incoming_letter';
    case ViewOutgoingLetter = 'view_outgoing_letter';
    case AddOutgoingLetter = 'add_outgoing_letter';
    case EditOutgoingLetter = 'edit_outgoing_letter';
    case DeleteOutgoingLetter = 'delete_outgoing_letter';
    case ViewLetterCategory = 'view_letter_category';
    case AddLetterCategory = 'add_letter_category';
    case EditLetterCategory = 'edit_letter_category';
    case DeleteLetterCategory = 'delete_letter_category';
    case ViewDisposition = 'view_disposition';
    case AddDisposition = 'add_disposition';
    case EditDisposition = 'edit_disposition';
    case DeleteDisposition = 'delete_disposition';

    public static function values(): array
    {
        return array_map(fn (self $enum): string => $enum->value, self::cases());
    }

    public static function module(): array
    {
        return [
            'user', 'role', 'incoming_letter', 'outgoing_letter', 'letter_category', 'disposition',
        ];
    }

    public static function action(): array
    {
        return [
            'view', 'add', 'edit', 'delete',
        ];
    }

    public static function allows(): array
    {
        $allows = [];

        foreach (self::values() as $enum) {
            $allows[$enum] = Gate::allows($enum);
        }

        return $allows;
    }

    public function isAllowed(): bool
    {
        return Gate::allows($this->value);
    }

    public function asMiddleware(): string
    {
        return "can:" . $this->value;
    }
}
