<?php
    include_once 'classes.php';

    class PermissionsSystem {
        public function get(string $idUser) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `permissions` WHERE `id_account`='$idUser'");
                if ($consult) {
                    $datas = $consult->fetch_array();
                    return array('ok' => true, 'permission' => $datas['permission_type']);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
                
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function setNew($idAccount) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("INSERT INTO `permissions`(`id`, `id_account`, `id_declare`, `permission_type`) VALUES (NULL, $idAccount, 0, 0)");
                if ($consult) {
                    return true;
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function update($idDeclare, $idAccount, string $permission) {
            try {
                $db = new DBSystem();
                
                $verifyAccount = $this->verifyAccount($idDeclare, 3);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                
                $permissionDeclare = $this->get($idDeclare);
                $permissionAccount = $this->get($idAccount);
                
                if (!$permissionAccount['ok']) return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
                if (intval($permissionDeclare['permission']) < intval($permissionAccount['permission'])) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');

                if (!$permissionDeclare['ok']) return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
                if (intval($permissionDeclare['permission']) < intval($permission)) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                
                $consult = $db->Query("UPDATE `permissions` SET `id_declare`=$idDeclare,`permission_type`='$permission' WHERE `id_account`='$idAccount'");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al intentar cambiar los permisos del usuario.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        public function verifyAccount($idAccount, $permission) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `permissions` WHERE `id_account`=$idAccount");
                if ($consult) {
                    $arrayData = $consult->fetch_array();
                    return ((int) $arrayData['permission_type'] >= $permission)? true: false;
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        public function deletePermissions(string $idAccount) {
            try {
                $db = new DBSystem();
                $consult1 = $db->Query("SELECT * FROM `permissions` WHERE `id_account`=$idAccount");
                if ($consult1) {
                    if ($consult1->num_rows !== 0) {
                        $arrayConsult1 = $consult1->fetch_array();
                        $idConsult1 = $arrayConsult1['id'];
                        $consult2 = $db->Query("DELETE FROM `permissions` WHERE `id`=$idConsult1");
                        if ($consult2) {
                            return true;
                        } else {
                            return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                        }
                    } else {
                        return true;
                    }
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        public function getListAdmins(string $idAccount) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $account = new AccountSystem();
                $verifyAccount = $permission->verifyAccount($idAccount, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("SELECT * FROM `permissions`");
                if ($consult) {
                    $arrayData = array();
                    while ($permission = $consult->fetch_array()) {
                        $dataClient = $account->getAdminDataUser($idAccount, $permission['id_account']);
                        array_push($arrayData, array(
                            'id' => $permission['id'],
                            'idUser' => $permission['id_account'],
                            'idDeclare' => $permission['id_declare'],
                            'permission' => $permission['permission_type'],
                            'accountData' => array(
                                'name' => $dataClient['data']['name'],
                                'image' => $dataClient['data']['image'],
                                'birthday' => $dataClient['data']['birthday'],
                            )
                        ));
                    }
                    return array('ok' => true, 'cause' => '', 'permissions' => $arrayData);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
    }
    
?>