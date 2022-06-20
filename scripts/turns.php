<?php
    include_once 'classes.php';

    class TurnsSystem {
        public function create($idDeclare, $idAccount, string $day, string $schedule, string $type, string $commission) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("INSERT INTO `turns`(`id`, `id_account`, `day`, `schedule`, `type`, `commission`) VALUES (NULL, $idAccount, '$day', '$schedule', '$type', '$commission')");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al formar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function delete($idTurn, $idDeclare) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("DELETE FROM `turns` WHERE `id`=$idTurn");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al formar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function update($idTurn, $idDeclare, $idAccount, string $day, string $schedule, string $type, string $commission) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("UPDATE `turns` SET `id_account`=$idAccount,`day`=$day,`schedule`=$schedule,`type`=$type,`commission`=$commission WHERE `id`=$idTurn");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al formar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function get($idTurn) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `turns` WHERE `id`=$idTurn");
                if ($consult) {
                    $arrayData = $consult->fetch_array();
                    $dataUser = $this->getDataUser($arrayData['id_account']);
                    if (!$dataUser['ok']) return $dataUser;
                    return array(
                        'ok' => true,
                        'cause' => '',
                        'data' => array(
                            'account' => $dataUser['data'],
                            'day' => $arrayData['day'],
                            'schedule' => $arrayData['schedule'],
                            'type' => $arrayData['type'],
                            'commission' => $arrayData['commission']
                        )
                    );
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al formar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function getAllOne($idAccount) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `turns` WHERE `id_account`=$idAccount");
                if ($consult) {
                    $arrayData = array();
                    $dataUser = $this->getDataUser($idAccount);
                    if (!$dataUser['ok']) return $dataUser;
				    while ($turn = $consult->fetch_array()) {
                        array_push($arrayData, array(
                            'account' => $dataUser['data'],
                            'day' => $turn['day'],
                            'schedule' => $turn['schedule'],
                            'type' => $turn['type'],
                            'commission' => $turn['commission']
                        ));
                    }
                    return array('ok' => true, 'cause' => '', 'data' => $arrayData);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al formar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function getAll() {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `turns`");
                if ($consult) {
                    $arrayData = array();
				    while ($turn = $consult->fetch_array()) {    
                        $dataUser = $this->getDataUser($turn['id_account']);
                        if (!$dataUser['ok']) return $dataUser;
                        array_push($arrayData, array(
                            'account' => $dataUser['data'],
                            'day' => $turn['day'],
                            'schedule' => $turn['schedule'],
                            'type' => $turn['type'],
                            'commission' => $turn['commission']
                        ));
                    }
                    return array('ok' => true, 'cause' => '', 'data' => $arrayData);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al formar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        private function getDataUser($idAccount) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT `id`, `name`, `email`, `birthday`, `dni`, `phone`, `experience`, `image` FROM `accounts` WHERE `id`=$idAccount");
                if ($consult) {
                    $arrayData = $consult->fetch_array();
                    return array(
                        'ok' => true,
                        'cause' => '',
                        'data' => array(
                            'id' => $arrayData['id'],
                            'name' => $arrayData['name'],
                            'email' => $arrayData['email'],
                            'birthday' => $arrayData['birthday'],
                            'dni' => $arrayData['dni'],
                            'phone' => $arrayData['phone'],
                            'experience' => $arrayData['experience'],
                            'image' => $arrayData['image']
                        )
                    );
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al formar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
    }
    
?>