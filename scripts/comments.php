<?php
    include_once 'classes.php';

    class CommentsSystem {
        public function create($idDeclare, $idTraining, $idAccount, string $comment, string $date) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("INSERT INTO `comments`(`id`, `id_training`, `id_account`, `id_issuer`, `comment`, `date`, `edit`) VALUES (NULL, $idTraining, $idAccount, $idDeclare, '$comment', '$date', '0')");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function update($idDeclare, $idComment, string $comment, string $date) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("UPDATE `comments` SET `id_issuer`='$idDeclare',`comment`='$comment',`date`='$date', `edit`='1' WHERE `id`=$idComment");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function delete($idDeclare, $idComment) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idDeclare, 1);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("DELETE FROM `comments` WHERE `id`=$idComment");
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
                $account = new AccountSystem();
                $training = new TrainingSystem();
                $consult = $db->Query("SELECT * FROM `comments` WHERE `id_account`=$idAccount");
                if ($consult) {
                    $arrayData = array();
				    while ($comment = $consult->fetch_array()) {
                        $dataClient = $account->system_getDataUser($comment['id_issuer']);
                        $dateTraining = $training->system_getDateTraining($comment['id_training']);
                        array_push($arrayData, array(
                            'id' => $comment['id'],
                            'id_training' => $comment['id_training'],
                            'id_issuer' => $comment['id_issuer'],
                            'comment' => $comment['comment'],
                            'date' => $comment['date'],
                            'date_training' => $dateTraining,
                            'accountData' => array(
                                'name' => $dataClient['data']['name'],
                                'image' => $dataClient['data']['image'],
                                'birthday' => $dataClient['data']['birthday'],
                            )
                        ));
                    }
                    usort($arrayData, function($a, $b) {
                        $date1 = base64_decode($a["date_training"]);
                        $date2 = base64_decode($b["date_training"]);
                        
                        $e1 = explode("/", $date1);
                        $e2 = explode("/", $date2);

                        $date1_2 = $e1[0]."-".$e1[1]."-".$e1[2];
                        $date2_2 = $e2[0]."-".$e2[1]."-".$e2[2];

                        $time1 = strtotime($date1_2);
                        $time2 = strtotime($date2_2);

                        return $time1 - $time2;
                    });
                    return array('ok' => true, 'cause' => '', 'data' => $arrayData);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        public function deleteCommentsAdmin(string $idAccount) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `comments` WHERE `id_account`=$idAccount");
                if ($consult) {
				    while ($comment = $consult->fetch_array()) {
                        $idComment = $comment['id'];
                        $delete = $db->Query("DELETE FROM `comments` WHERE `id`=$idComment");
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

        public function deleteSystem($idTraining) {
            try {
                $db = new DBSystem();
                $consult = $db->Query("SELECT * FROM `comments` WHERE `id_training`=$idTraining");
                if ($consult) {
                    while ($comment = $consult->fetch_array()) {
                        $idComment = $comment['id'];
                        $consult2 = $db->Query("DELETE FROM `comments` WHERE `id`=$idComment");
                        if (!$consult2) return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                    }
                    return true;
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        public function admin_deleteComment(string $idAdmin, string $idComment) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idAdmin, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => "No posees los permisos suficientes para realizar esta acción. (Usuario: $idAdmin)");
                $consult = $db->Query("DELETE FROM `comments` WHERE `id`='$idComment'");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
        
        public function admin_editComment(string $idAdmin, string $idComment, string $newComment, string $date) {
            try {
                $db = new DBSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idAdmin, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("UPDATE `comments` SET `id_issuer`=$idAdmin, `comment`='$newComment', `date`='$date' WHERE `id`=$idComment");
                if ($consult) {
                    return array('ok' => true, 'cause' => '');
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al consultar la base de datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }

        public function admin_getAll($idAdmin, $idAccount) {
            try {
                $db = new DBSystem();
                $account = new AccountSystem();
                $permission = new PermissionsSystem();
                $verifyAccount = $permission->verifyAccount($idAdmin, 2);
                if (is_object($verifyAccount)) return $verifyAccount;
                if (!$verifyAccount) return array('ok' => false, 'cause' => 'No posees los permisos suficientes para realizar esta acción.');
                $consult = $db->Query("SELECT * FROM `comments` WHERE `id_account`=$idAccount");
                if ($consult) {
                    $arrayData = array();
				    while ($comment = $consult->fetch_array()) {
                        $dataClient = $account->system_getDataUser($comment['id_issuer']);
                        array_push($arrayData, array(
                            'id' => $comment['id'],
                            'id_training' => $comment['id_training'],
                            'id_issuer' => $comment['id_issuer'],
                            'comment' => $comment['comment'],
                            'date' => $comment['date'],
                            'edit' => ($comment['edit'] == '1')? true: false,
                            'accountData' => array(
                                'name' => $dataClient['data']['name'],
                                'image' => $dataClient['data']['image'],
                                'birthday' => $dataClient['data']['birthday'],
                            )
                        ));
                    }
                    return array('ok' => true, 'cause' => '', 'data' => $arrayData);
                } else {
                    return array('ok' => false, 'cause' => 'Ocurrió un error al guardar los datos.');
                }
            } catch (\Throwable $th) {
                return array('ok' => false, 'cause' => 'Ocurrió un error de parte del servidor.');
            }
        }
    }
    
?>