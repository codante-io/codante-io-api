<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ["id"];

    public static $allowedReactionTypes = [
        "like",
        "exploding-head",
        "fire",
        "rocket",
    ];

    public static function validateReactable($reactableType)
    {
        $reactableClass = "App\\Models\\" . $reactableType;

        // check if reactable model exists, if not, return error
        if (!class_exists($reactableClass)) {
            return response()->json(
                [
                    "message" => "Reactable model does not exist",
                ],
                404
            );
        }

        // check if the model is reactable
        if (!in_array("App\\Traits\\Reactable", class_uses($reactableClass))) {
            return response()->json(
                [
                    "message" => "Model is not reactable",
                ],
                404
            );
        }

        return $reactableClass;
    }

    public static function getReactions($reactableClass, $reactableId)
    {
        // find if the reactable model exists
        $reactable = $reactableClass::findOrFail($reactableId);

        // get the reactions count by type
        $reactions = $reactable
            ->reactions()
            ->selectRaw("reaction, count(*) as count")
            ->groupBy("reaction")
            ->get();

        // get the user specific reactions
        if (!auth("sanctum")->user()) {
            return ["reaction_counts" => $reactions];
        }

        // get user reactions in an array of types
        $userReactions = $reactable
            ->reactions()
            ->where("user_id", auth("sanctum")->user()->id)
            ->pluck("reaction")
            ->toArray();

        return [
            "reaction_counts" => $reactions,
            "user_reactions" => $userReactions,
        ];
    }
}
