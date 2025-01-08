<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

class Reaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public static $allowedReactionTypes = [
        'like',
        'exploding-head',
        'fire',
        'rocket',
    ];

    public static function validateReactable($reactableType)
    {
        $reactableClass = 'App\\Models\\'.$reactableType;

        $validator = Validator::make(
            ['reactable_type' => $reactableClass],
            [
                'reactable_type' => [
                    function ($attribute, $value, $fail) {
                        if (! class_exists($value)) {
                            $fail('Reactable model does not exist.');
                        } elseif (
                            ! in_array(
                                'App\\Traits\\Reactable',
                                class_uses($value)
                            )
                        ) {
                            $fail('Model is not reactable.');
                        }
                    },
                ],
            ]
        );

        $validator->validate();

        return $reactableClass;
    }

    public static function getReactions($reactableClass, $reactableId)
    {
        // find if the reactable model exists
        $reactable = $reactableClass::findOrFail($reactableId);

        // get the reactions count by type
        $reactions = $reactable
            ->reactions()
            ->selectRaw('reaction, count(*) as count')
            ->groupBy('reaction')
            ->get();

        // get the user specific reactions
        if (! auth('sanctum')->user()) {
            return ['reaction_counts' => $reactions];
        }

        // get user reactions in an array of types
        $userReactions = $reactable
            ->reactions()
            ->where('user_id', auth('sanctum')->user()->id)
            ->pluck('reaction')
            ->toArray();

        return [
            'reaction_counts' => $reactions,
            'user_reactions' => $userReactions,
        ];
    }
}
