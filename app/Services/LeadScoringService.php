<?php

namespace App\Services;

use App\Models\Lead;

class LeadScoringService
{
    public function getLeadScore(Lead $lead): int
    {
        // Por simplicidad, se devolverá un número aleatorio para simular el scoring
        return rand(1, 100);
    }
}
