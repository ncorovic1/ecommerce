<?php

namespace App\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'identifier'   => (int)$user->id,
            'name'         => (string)$user->name,
            'email'        => (string)$user->email,
            'isVerified'   => (int)$user->verified,
            'isAdmin'      => ($user->admin === User::ADMIN_USER),
            'creationDate' => (string)$user->created_at,
            'lastChange'   => (string)$user->updated_at,
            'deletedDate'  => isset($user->deleted_at) ? (string)$user->deleted_at : null,

            'links' => [
                [
                    'rel'  => 'self',
                    'href' => route('users.show', $user->id),
                ],
            ],
        ];
    }

    public static function originalAttribute($att) {
        $attributes =  [
            'identifier'            => "id",
            'name'                  => "name",
            'email'                 => "email",
            'password'              => "password",
            'password_confirmation' => "password_confirmation",
            'isVerified'            => "verified",
            'isAdmin'               => "admin",
            'creationDate'          => "created_at",
            'lastChange'            => "updated_at",
            'deletedDate'           => "deleted_at",
        ];

        return isset($attributes[$att]) ? $attributes[$att] : null;
    }

    public static function transformedAttribute($att) {
        $attributes =  [
            'id'                    => "identifier",
            'name'                  => "name",
            'email'                 => "email",
            'password'              => "password",
            'password_confirmation' => "password_confirmation",
            'verified'              => "isVerified",
            'admin'                 => "isAdmin",
            'created_at'            => "creationDate",
            'updated_at'            => "lastChange",
            'deleted_at'            => "deletedDate",
        ];

        return isset($attributes[$att]) ? $attributes[$att] : null;
    }
}
