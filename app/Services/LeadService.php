<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Client;
use App\Services\LeadScoringService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Lead Service.
 */
class LeadService
{
    /**
     * Servicio para obtener el puntaje (score) de un Lead.
     */
    private LeadScoringService $leadScoringService;

    /**
     * Constructor del servicio.
     *
     * @param LeadScoringService $leadScoringService Servicio para calcular el puntaje de los Leads.
     */
    public function __construct(LeadScoringService $leadScoringService)
    {
        $this->leadScoringService = $leadScoringService;
    }

    /**
     * Crear un nuevo Lead y asignarle un puntaje.
     *
     * @param array $data Datos del Lead a crear.
     * @return Lead El Lead creado.
     */
    public function createLead(array $data): Lead
    {
        $lead = Lead::create($data);

        Client::create([
            'email' => $lead->email,
            'lead_id' => $lead->id
        ]);

        $score = $this->leadScoringService->getLeadScore($lead);
        $lead->score = $score;
        $lead->save();

        return $lead;
    }

    /**
     * Obtener un Lead específico por su ID.
     *
     * @param int $idLead El identificador del Lead.
     * @return Lead El Lead encontrado.
     * @throws ModelNotFoundException Si no se encuentra el Lead.
     */
    public function getLead(int $idLead): Lead
    {
        if (!$lead = Lead::find($idLead)) {
            throw new ModelNotFoundException("Lead no encontrado");
        }
        return $lead;
    }

    /**
     * Actualizar un Lead y obtiene un nuevo puntaje.
     *
     * @param int $idLead El identificador del Lead a actualizar.
     * @param array $data Datos para actualizar el Lead.
     * @return Lead El Lead actualizado.
     */
    public function updateLead(int $idLead, array $data): Lead
    {
        $lead = $this->getLead($idLead);
        $lead->update($data);

        // Obtener nuevo puntaje tras la actualización
        $score = $this->leadScoringService->getLeadScore($lead);
        $lead->score = $score;
        $lead->save();

        return $lead;
    }

    /**
     * Eliminar un Lead específico.
     *
     * @param int $idLead El identificador del Lead a eliminar.
     * @return void
     */
    public function deleteLead(int $idLead): void
    {
        $lead = $this->getLead($idLead);
        $lead->delete();
    }
}
