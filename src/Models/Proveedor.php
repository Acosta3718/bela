<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Proveedor extends Model
{
    protected string $table = 'proveedores';

    protected array $fillable = [
        'nombre',
        'documento',
        'direccion',
        'telefono',
        'estado',
    ];

    public function buscar(string $termino, int $limite = 20, bool $soloActivos = false): array
    {
        $termino = trim($termino);
        $where = [];
        $params = [];

        if ($termino !== '') {
            $terminoNormalizado = function_exists('mb_strtolower') ? mb_strtolower($termino) : strtolower($termino);
            $like = '%' . $terminoNormalizado . '%';
            $where[] = '(LOWER(nombre) LIKE :termino OR LOWER(documento) LIKE :termino OR telefono LIKE :termino)';
            $params['termino'] = $like;
        }

        if ($soloActivos) {
            $where[] = "estado = 'activo'";
        }

        $sql = "SELECT * FROM {$this->table}";
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY nombre LIMIT :limite';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}