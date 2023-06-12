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

        $reactableClass = Reaction::validateReactable($data["reactable_type"]);

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

        $reactableClass = Reaction::validateReactable($data["reactable_type"]);

        return Reaction::getReactions($reactableClass, $data["reactable_id"]);
    }
}
