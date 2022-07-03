<?php
    include_once 'classes.php';

    class TrainingSystem {
        public function create($idDeclare, $idAccount, $idExercise, string $date, string $session_number, string $rds, string $rpe, string $pulse, string $repetitions, string $kilage, string $tonnage) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $notifications = new NotificationSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->QueryAndConect("INSERT INTO `training`(`id`, `id_account`, `id_exercise`, `date`, `session_number`, `rds`, `rpe`, `pulse`, `repetitions`, `kilage`, `tonnage`) VALUES (NULL, $idAccount, $idExercise, '$date', '$session_number', '$rds', '$rpe', '$pulse', '$repetitions', '$kilage', '$tonnage')");
                if ($consult['exec']) {
                    $notifications->send($idAccount, 'Se han actualizado sus estadísticas', 'Toque aquí para ver');
                    return array('ok' => true, 'cause' => '', 'datas' => $consult['connection']->insert_id);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.', 'error' => $th);
            }
        }
        public function update($idDeclare, $idTraining, string $session_number, string $rds, string $rpe, string $pulse, string $repetitions, string $kilage, string $tonnage) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("UPDATE `training` SET `session_number`='$session_number',`rds`='$rds',`rpe`='$rpe',`pulse`='$pulse',`repetitions`='$repetitions',`kilage`='$kilage',`tonnage`='$tonnage' WHERE `id`=$idTraining");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function delete($idDeclare, $idTraining) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("DELETE FROM `training` WHERE `id`=$idTraining");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function getAll($idAccount) {
            try {
                $db = new DBSystem();
                $exercise = new ExerciseSystem();
                $consult = $db->Query("SELECT * FROM `training` WHERE `id_account`=$idAccount");
                if ($consult) {
                    $arrayData = array();
				    while ($training = $consult->fetch_array()) {
                        $dataExercise = $exercise->system_get($training['id_exercise']);
                        if (!$dataExercise) return  array('ok' => false, 'cause' => 'Ocurrió un error al leer los datos.');
                        array_push($arrayData, array(
                            'id' => $training['id'],
                            'date' => $training['date'],
                            'session_number' => $training['session_number'],
                            'rds' => $training['rds'],
                            'rpe' => $training['rpe'],
                            'pulse' => $training['pulse'],
                            'repetitions' => $training['repetitions'],
                            'kilage' => $training['kilage'],
                            'tonnage' => $training['tonnage'],
                            'exercise' => $dataExercise
                        ));
                    }
                    usort($arrayData, function($a, $b) {
                        $date1 = base64_decode($a["date"]);
                        $date2 = base64_decode($b["date"]);
                        $e1 = explode("/", $date1);
                        $e2 = explode("/", $date2);
                        $date1_2 = $e1[0]."-".$e1[1]."-".$e1[2];
                        $date2_2 = $e2[0]."-".$e2[1]."-".$e2[2];
                        $time1 = strtotime($date1_2);
                        $time2 = strtotime($date2_2);
                        return $time1 - $time2;
                    });
                    return array('ok' => true, 'cause' => '', 'trainings' => $arrayData);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al leer los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function deleteTrainingsAdmin(string $idAccount) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `training` WHERE `id_account`=$idAccount");
                if ($consult) {
				    while ($training = $consult->fetch_array()) {
                        $idTraining = $training['id'];
                        $delete = $db->Query("DELETE FROM `training` WHERE `id`=$idTraining");
                        if (!$delete) return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                    }
                    return true;
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function admin_getAllUser($idAdmin, $idAccount) {
            try {
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idAdmin, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                return $this->getAll($idAccount);
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function admin_delete($idAdmin, $idTraining) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $comments = new CommentsSystem();
                $verifyAccount = $permission->verifyAccount($idAdmin, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("DELETE FROM `training` WHERE `id`=$idTraining");
                if ($consult) {
                    $deleteComments = $comments->deleteSystem($idTraining);
                    if (is_object($deleteComments)) return $deleteComments;
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function system_gerNumbersOfTrainings($idUser) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `training` WHERE `id_account`=$idUser");
                if ($consult) {
                    return $consult->num_rows;
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function system_getDateTraining($idTraining) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `training` WHERE `id`=$idTraining");
                if ($consult) {
                    $datas = $consult->fetch_array();
                    return $datas['date'];
                }
                return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
    }
    
?>