<?php

class DBSystem {
    private String $user = "corporal_scapps";
    private String $password = "SCApi-2022";
    private String $database = "corporal_api";
    public function Query(string $sql) {
        $conexion = new mysqli("localhost", $this->user, $this->password, $this->database) or die ("No se pudo conectar");
		$exec = $conexion->query($sql);
		return $exec;
    }
    public function QueryAndConect(string $sql) {
        $conexion = new mysqli("localhost", $this->user, $this->password, $this->database) or die ("No se pudo conectar");
		$exec = $conexion->query($sql);
		return array(
            'exec' => $exec,
            'connection' => $conexion
        );
    }
}


?>