<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Funcionario;
use App\Models\Pago;
use App\Models\Venta;

class PagosController extends Controller
{
    protected Pago $model;
    protected Funcionario $funcionario;
    protected Venta $venta;

    public function __construct()
    {
        $this->model = new Pago();
        $this->funcionario = new Funcionario();
        $this->venta = new Venta();
    }

    public function index()
    {
        $pagos = $this->model->all();
        $funcionarios = $this->funcionario->all();
        return $this->view('pagos/index', compact('pagos', 'funcionarios'));
    }

    public function create()
    {
        $funcionarios = $this->funcionario->all();
        $ventas = $this->venta->all();
        return $this->view('pagos/create', compact('funcionarios', 'ventas'));
    }

    public function store()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'funcionario_id' => 'required',
            'venta_id' => 'required',
            'monto' => 'required',
            'fecha_pago' => 'required',
            'periodo_inicio' => 'required',
            'periodo_fin' => 'required'
        ]);

        if ($errors) {
            $funcionarios = $this->funcionario->all();
            $ventas = $this->venta->all();
            $pago = $data;
            return $this->view('pagos/create', compact('errors', 'pago', 'funcionarios', 'ventas'));
        }

        $this->model->create($data);
        return $this->redirect('/pagos');
    }

    public function destroy()
    {
        $id = (int)Request::get('id');
        $this->model->delete($id);
        return $this->redirect('/pagos');
    }
}