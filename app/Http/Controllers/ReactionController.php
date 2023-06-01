<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    public function toggle()
    {
        $data = request()->validate([
            "reactable_id" => "required",
            "reactable_type" => "required",
            "reaction" =>
                "required|in:" . implode(",", Reaction::$allowedReactionTypes),
        ]);

        $reactableClass = "App\\Models\\" . $data["reactable_type"];

        $this->validateReactable($reactableClass);

        // find if the reactable model exists
        $reactable = $reactableClass::findOrFail($data["reactable_id"]);

        // if the user has already reacted, delete the reaction
        if (
            $reactable->isReactedBy($data["reaction"], auth("sanctum")->user())
        ) {
            $reactable->removeReaction(
                $data["reaction"],
                auth("sanctum")->user()
            );
            return response()->json([
                "message" => "Reaction removed successfully",
                "result" => "destroy",
                "reaction" => $data["reaction"],
            ]);
        }

        // create the reaction
        $reactable->react($data["reaction"], auth("sanctum")->user());

        return response()->json([
            "message" => "Reaction created successfully",
            "result" => "create",
            "reaction" => $data["reaction"],
        ]);
    }

    public function getReactions(Request $request)
    {
        // validate request
        $data = $request->validate([
            "reactable_id" => "required",
            "reactable_type" => "required",
        ]);

        $reactableClass = "App\\Models\\" . $data["reactable_type"];
        $this->validateReactable($reactableClass);

        // find if the reactable model exists
        $reactable = $reactableClass::findOrFail($data["reactable_id"]);

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

    protected function validateReactable($reactableClass)
    {
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
    }
}
