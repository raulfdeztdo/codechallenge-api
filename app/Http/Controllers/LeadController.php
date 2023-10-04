<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\LeadService;

/**
 * Lead Controller.
 */
class LeadController extends Controller
{
    /**
     * Constructor del controlador.
     *
     * @param LeadService $leadService El servicio para gestionar los Leads.
     */
    public function __construct(private LeadService $leadService)
    {
    }

    /**
     * Listar todos los Leads.
     *
     * @return \Illuminate\Database\Eloquent\Collection|Lead[] Listado de Leads.
     */
    public function index()
    {
        return Lead::all();
    }

    /**
     * Almacenar un nuevo Lead.
     *
     * @param Request $request Los datos del Lead a crear.
     * @return \Illuminate\Http\JsonResponse La respuesta después de crear un Lead.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:clients',
                'phone' => 'nullable|numeric|digits_between:5,10',
            ]);

            $lead = $this->leadService->createLead($data);

            return response()->json([
                'message' => 'Lead creado con éxito',
                'lead' => $lead
            ], Response::HTTP_CREATED);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], $e->status);
        }
    }

    /**
     * Mostrar un Lead específico.
     *
     * @param int $idLead El identificador del Lead a mostrar.
     * @return \Illuminate\Http\JsonResponse Los detalles del Lead o un mensaje de error.
     */
    public function show($idLead)
    {
        try {
            $lead = $this->leadService->getLead($idLead);
            return response()->json($lead);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lead no encontrado'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Actualizar un Lead específico.
     *
     * @param Request $request Los datos a actualizar.
     * @param int $idLead El identificador del Lead a actualizar.
     * @return \Illuminate\Http\JsonResponse La respuesta después de actualizar el Lead.
     */
    public function update(Request $request, $idLead)
    {
        $currentEmail = Lead::find($idLead)->email;
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|numeric|digits_between:5,10',
        ];
        if ($currentEmail != $request->input('email')) {
            $rules['email'] = 'required|email|max:255|unique:clients,email';
        } else {
            $rules['email'] = 'required|email|max:255';
        }

        try {
            $data = $request->validate($rules);

            $lead = $this->leadService->updateLead($idLead, $data);
            return response()->json(['message' => 'Lead actualizado con éxito', 'lead' => $lead]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], $e->status);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lead no encontrado'], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Eliminar un Lead específico.
     *
     * @param int $idLead El identificador del Lead a eliminar.
     * @return \Illuminate\Http\JsonResponse La respuesta después de eliminar el Lead.
     */
    public function destroy($idLead)
    {
        try {
            $this->leadService->deleteLead($idLead);
            return response()->json(['message' => 'Lead eliminado con éxito']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Lead no encontrado'], Response::HTTP_NOT_FOUND);
        }
    }
}
