<?php

// /////////////////////////////////////////////////////////////////////////////
// PLEASE DO NOT RENAME OR REMOVE ANY OF THE CODE BELOW. 
// YOU CAN ADD YOUR CODE TO THIS FILE TO EXTEND THE FEATURES TO USE THEM IN YOUR WORK.
// /////////////////////////////////////////////////////////////////////////////
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;
use App\Http\Requests\StorePlayerRequest;
use App\Http\Requests\UpdatePlayerRequest;
use App\Models\PlayerSkill;

class PlayerController extends Controller
{
    public function index()
    {
        $players = Player::all();

        if (!$players) {
            return response("Failed", 500);
        }

        return response()->json($players, 200, [], JSON_PRETTY_PRINT);
    }

    public function store(StorePlayerRequest $request)
    {
        $playerData = $request->all();
        $playerSkillsData = $playerData['playerSkills']; 
        
        if (empty($playerSkillsData)) {
            return response("Failed", 500);
        }
        
        unset($playerData['playerSkills']); 
        
        $player = Player::create($playerData); 
        
        if (!$player) {
            return response("Failed", 500);
        }
        
        $playerId = $player->id;
        
        foreach ($playerSkillsData as &$skillData) {
            $skillData['player_id'] = $playerId;
        }
        
        PlayerSkill::insert($playerSkillsData);
        
        return response()->json([
            'res' => true,
            'msg' => 'Player added successfully'
        ],200);
    }

    public function update(UpdatePlayerRequest $request, $id)
    {
        $playerData = $request->all();
        $playerSkillsData = $playerData['playerSkills'];

        $player = Player::findOrFail($id);

        if (empty($playerSkillsData)) {
            return response("Failed: No skills provided", 400);
        }
    
        unset($playerData['playerSkills']);

        $updated = $player->update($playerData);

        if (!$updated) {
            return response("Failed to update player", 500);
        }
    
        PlayerSkill::where('player_id', $player->id)->delete();
    
        $playerSkills = [];
        foreach ($playerSkillsData as $skillData) {
            $playerSkills[] = [
                'skill' => $skillData['skill'],
                'value' => $skillData['value'],
                'player_id' => $player->id
            ];
        }
    
        PlayerSkill::insert($playerSkills);
    
        return response()->json([
            'res' => true,
            'msg' => 'Player updated successfully'
        ], 200);
    }

    public function destroy($id)
    {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
 
        if (!$token || $token !== 'Bearer SkFabTZibXE1aE14ckpQUUxHc2dnQ2RzdlFRTTM2NFE2cGI4d3RQNjZmdEFITmdBQkE=') {
            return response("Unauthorized", 401);
        }
    
        $player = Player::find($id);
    
        if (!$player) {
            return response("Failed", 500);
        }
    
        $player->delete();
        PlayerSkill::where('player_id', $player->id)->delete();
    
        return response()->json([
            'res' => true,
            'msg' => 'Player deleted successfully'
        ]);
    }
}
