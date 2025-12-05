<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Concepto extends Model
{
    protected string $table = 'conceptos';

    protected array $fillable = [
        'nombre',
        'estado',
    ];

    public function activos(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE estado = 'activo' ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscar(string $termino, int $limite = 20, bool $soloActivos = false): array
    {
        $termino = trim($termino);
        $params = [];
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";

        if ($soloActivos) {
            $sql .= " AND estado = 'activo'";
        }

        if ($termino !== '') {
            $sql .= ' AND LOWER(nombre) LIKE :termino';
            $params['termino'] = '%' . strtolower($termino) . '%';
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