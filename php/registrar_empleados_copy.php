<?php 
    include("conex_db.php");  // conectar la base de datos
    require '../vendor/autoload.php'; // Para PHPMailer y QR code

    use Endroid\QrCode\QrCode;
    use Endroid\QrCode\Writer\PngWriter;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $mensaje = ""; // variable para almacenar el mensaje de respuesta

    if (isset($_POST['btn_register'])) {  
        if (!empty($_POST['nombre']) && !empty($_POST['correo']) && !empty($_POST['usuario']) && 
            !empty($_POST['contrasena'])) {

                    function generarID() {
                        $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $numeros = '0123456789';
                        
                        // Generar 3 letras aleatorias
                        $letrasAleatorias = '';
                        for ($i = 0; $i < 3; $i++) {
                            $letrasAleatorias .= $letras[rand(0, strlen($letras) - 1)];
                        }
                    
                        // Generar 3 números aleatorios
                        $numerosAleatorios = '';
                        for ($i = 0; $i < 3; $i++) {
                            $numerosAleatorios .= $numeros[rand(0, strlen($numeros) - 1)];
                        }
                    
                        // Combinar letras y números
                        $combinado = $letrasAleatorias . $numerosAleatorios;
                    
                        // Mezclar el resultado aleatoriamente
                        $idAleatoria = str_shuffle($combinado);
                    
                        return $idAleatoria;
                    }
                    
                    $id = generarID();                
    
                    $nombre = trim($_POST['nombre']);
                    $correo = trim($_POST['correo']);
                    $usuario = trim($_POST['usuario']);
                    $contrasena = password_hash(trim($_POST['contrasena']), PASSWORD_DEFAULT);
                    $fechareg = date("Y-m-d");
        
                    // Verificar si el correo ya existe
                    $consulta_correo = "SELECT * FROM login_db WHERE correo = '$correo'";
                    $resultado_correo = mysqli_query($conexion, $consulta_correo);
                    if (mysqli_num_rows($resultado_correo) > 0) {
                        echo '<script>
                        alert("El correo electrónico ya está registrado.");
                        window.location.href = "../html/registro_inicio_sesion.html?error=email_existe";
                        </script>';
                        exit();
                    } else {

                        if (strlen($_POST['contrasena']) >= 6 && strlen($_POST['contrasena']) <= 15){
                            // condicion  si la contrasena cumple los 6 digitos pero no mas de 15 digitos

                            $consulta = "INSERT INTO login_db (id, nombre, contrasena, apellido, correo, fecha_reg)
                                VALUES ('$id','$nombre','$contrasena','$usuario','$correo','$fechareg')";
        
                            $resultado = mysqli_query($conexion, $consulta);
            
                            if ($resultado) {
                                // Generar el código QR
                                $qrCode = new QrCode($id);
                                $writer = new PngWriter();
                                $qrResult = $writer->write($qrCode);
                                
                                // Guardar QR temporalmente
                                $qrTempPath = 'temp_qr/' . $id . '.png';
                                if (!file_exists('temp_qr')) {
                                    mkdir('temp_qr', 0777, true);
                                }
                                $qrResult->saveToFile($qrTempPath);
                                
                                // Enviar por correo
                                $mail = new PHPMailer(true);
                                
                                try {
                                    // Configuración del servidor SMTP
                                    $mail->isSMTP();
                                    $mail->Host = 'smtp.gmail.com';   // Servidor SMTP
                                    $mail->SMTPAuth = true;
                                    $mail->Username = //CORREO QUE DESEA UTILIZAR PARA ENVIAR EL CORREO 
                                    $mail->Password = //CONTRASEÑA DEL CORREO QUE DESEA UTILIZAR PARA ENVIAR EL CORREO 
                                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                    $mail->Port = 587;
                                    
                                    // Remitente
                                    $mail->setFrom('nvelezcuauro@gmail.com', 'Sistema de Acceso');
                                    
                                    // Destinatario
                                    $mail->addAddress($correo, $nombre);
                                    
                                    // Contenido del correo
                                    $mail->isHTML(true);
                                    $mail->Subject = 'Tu credencial de acceso - ' . $id;
                                    $mail->Body    = '
                                        <h2>¡Bienvenido, ' . $nombre . '!</h2>
                                        <p>Tu registro en nuestro sistema ha sido exitoso. A continuación encontrarás tu credencial de acceso:</p>
                                        <p><strong>ID de empleado:</strong> ' . $id . '</p>
                                        <p>Adjunto encontrarás tu código QR personalizado que podrás usar para acceder a las instalaciones.</p>
                                        <p>Gracias por unirte a nuestro equipo.</p>
                                    ';
                                    $mail->AltBody = 'Bienvenido ' . $nombre . '. Tu ID de empleado es: ' . $id . '. Adjunto encontrarás tu código QR.';
                                    
                                    // Adjuntar QR
                                    $mail->addAttachment($qrTempPath, 'credencial_qr.png');
                                    
                                    $mail->send();
                                    
                                    // Eliminar QR temporal
                                    unlink($qrTempPath);
                                    
                                    $mensaje = "<h3 class='ok'>Registro completado. Se ha enviado tu código QR al correo: " . $correo . "</h3>";
                                    
                                } catch (Exception $e) {
                                    $mensaje = "<h3 class='ok'>Registro completado, pero hubo un error al enviar el correo: " . $mail->ErrorInfo . "</h3>";
                                }
                                
                                // redirección a la misma pagina para evitar el reenvio de datos
                                header("Location: ../html/registro_inicio_sesion.html?mensaje=" . urlencode($mensaje));
                                exit;
                            } else {
                                $mensaje = "<h3 class='bad'>Ocurrió un error: " . mysqli_error($conexion) . "</h3>";
                            }

                        } else{
                            echo '<script>
                            alert("Ingrese una contrasena mayor de 6 digitos pero no menos de 15 digitos");
                            window.location.href = "../html/registro_inicio_sesion.html?error=email_existe";
                            </script>';
                            exit();  // en caso de que la contrasena no contenga los digitos requeridos
                        }

                        // gracias henry deidad por desempolvar el codigo de capybank UwU
                        // tanqueando las validaciones de los empleados 
                        
                    }
                    
        } else {
            $mensaje = "<h3 class='bad'>Completa todos los campos</h3>";
        }
    }

    // si funciona krboneeeesssss 🗿
    // las 3 horas de codigo a prueba y error mas satisfactorias de mi vida

?>

