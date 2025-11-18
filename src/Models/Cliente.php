<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Cliente extends Model
{
    protected string $table = 'clientes';

    protected array $fillable = [
        'nombre',
        'email',
        'telefono',
        'notas'
    ];


    public function buscar(string $termino, int $limite = 20): array
    {
        $termino = trim($termino);

        if ($termino === '') {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY nombre LIMIT :limite");
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $terminoNormalizado = function_exists('mb_strtolower') ? mb_strtolower($termino) : strtolower($termino);
        $like = '%' . $terminoNormalizado . '%';
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}"
            . " WHERE LOWER(nombre) LIKE :termino"
            . " OR LOWER(email) LIKE :termino"
            . " OR telefono LIKE :termino"
            . " ORDER BY nombre"
            . " LIMIT :limite"
        );
        $stmt->bindValue(':termino', $like);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}