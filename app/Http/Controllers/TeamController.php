<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Player;

class TeamController extends Controller
{
    public function processTeamSelection(Request $request)
    {
        $requirements = $request->json()->all();

        $positions = [];
        $skillCombinations = [];
        foreach ($requirements as $requirement) {
            $position = $requirement['position'];
            $skill = $requirement['mainSkill'];
            $combination = $position . '-' . $skill;

            if (in_array($combination, $skillCombinations)) {
                return response()->json(['error' => 'The same combination of position and ability was specified multiple times'], 400);
            }

            $skillCombinations[] = $combination;
            $positions[] = $position;
        }

        $selectedPlayers = [];
        $selectedPlayerIds = [];
        $total = 0;
        foreach ($requirements as $requirement) {
            $position = $requirement['position'];
            $skill = $requirement['mainSkill'];
            $numberOfPlayers = $requirement['numberOfPlayers'];
            $total += $numberOfPlayers;
            $players = Player::where('position', $position)
                ->join('player_skills', 'players.id', '=', 'player_skills.player_id')
                ->where('player_skills.skill', $skill)
                ->whereNotIn('players.id', $selectedPlayerIds)
                ->select('players.*')
                ->orderByDesc('player_skills.value')
                ->orderBy('players.id')
                ->take($numberOfPlayers) 
                ->get();

            $playersCount = $players->count();
            if($playersCount == $numberOfPlayers){
                foreach ($players as $player) {
                    for ($i = 0; $i < $numberOfPlayers; $i++) {
                        if (!in_array($player->id, $selectedPlayerIds)) {
                            $selectedPlayers[] = $player;
                            $selectedPlayerIds[] = $player->id;
                        }
                    }
                }
            }else if($playersCount < $numberOfPlayers){
                foreach ($players as $player) {
                    for ($i = 0; $i < $numberOfPlayers; $i++) {
                        if (!in_array($player->id, $selectedPlayerIds)) {
                            $selectedPlayers[] = $player;
                            $selectedPlayerIds[] = $player->id;
                        }
                    }
                }
                $players = Player::where('position', $position)
                ->join('player_skills', 'players.id', '=', 'player_skills.player_id')
                ->where('player_skills.skill', '<>', $skill)                
                ->whereNotIn('players.id', $selectedPlayerIds)
                ->select('players.*')
                ->orderByDesc('player_skills.value')
                ->orderBy('players.id')
                ->take($numberOfPlayers - $playersCount)
                ->get();
                foreach ($players as $player) {
                    for ($i = 0; $i < $numberOfPlayers; $i++) {
                        if (!in_array($player->id, $selectedPlayerIds)) {
                            $selectedPlayers[] = $player;
                            $selectedPlayerIds[] = $player->id;
                        }
                    }
                }
            }
        }
        $total2 = 0; 
        foreach ($selectedPlayers as &$player) {
            $total2 + 1;
        }     
        if($total != $total2){
            return response()->json(['error' => 'Insufficient number of players for the position: ' . $position], 400);
        }
        foreach ($selectedPlayers as &$player) {
            unset($player['id']);  
            foreach ($player['skills'] as &$skill) {
                unset($skill['id']);
                unset($skill['player_id']);
            }      
        }        
        return response()->json($selectedPlayers);
    }
}