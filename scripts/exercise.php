<?php
    include_once 'classes.php';

    class ExerciseSystem {
        public function create($idDeclare, string $name, string $description) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("INSERT INTO `exercise`(`id`, `name`, `description`) VALUES (NULL, '$name', '$description')");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function delete($idDeclare, $idExercise) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                if ($this->system_isUsed($idExercise)) return array('ok' => false, 'cause' => 'No puedes eliminar este ejercicio porque se encuentra en uso.');
                $consult = $db->Query("DELETE FROM `exercise` WHERE `id`='$idExercise'");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function edit($idDeclare, $idExercise, string $name, string $description) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("UPDATE `exercise` SET `name`='$name',`description`='$description' WHERE `id`=$idExercise");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function show(String $idAdmin) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idAdmin, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("SELECT * FROM `exercise`");
                if ($consult) {
                    $exercises = array();
                    while ($exercise = $consult->fetch_array()) {
                        array_push($exercises, array(
                            'id' => $exercise['id'],
                            'name' => $exercise['name'],
                            'description' => $exercise['description']
                        ));
                    }
                    return array('ok' => true, 'cause' => '', 'data' => $exercises);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function system_get(string $idExercise) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `exercise` WHERE `id`='$idExercise'");
                if ($consult) {
                    $data = $consult->fetch_array();
                    if (count($data) == 0) {
                        return array(
                            'id' => '0',
                            'name' => base64_encode('No se encontro'),
                            'description' => base64_encode('none')
                        );
                    }
                    return array(
                        'id' => $data['id'],
                        'name' => $data['name'],
                        'description' => $data['description']
                    );
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        }
        public function system_isUsed(string $idExercise) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `training` WHERE `id_exercise`=$idExercise");
                if ($consult) {
                    return ($consult->num_rows !== 0)? true: false;
                } else {
                    return false;
                }
            } catch (\Throwable $th) {
                return false;
            }
        }
    }
    
?>