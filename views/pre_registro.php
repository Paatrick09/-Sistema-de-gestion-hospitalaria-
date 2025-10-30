<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../config/Database.php';

$tipo_sangre = []; // Inicializamos la variable para evitar errores si no hay POST

// Consultar los tipos de sangre desde la base de datos
try {
    $database = new Database();
    $db = $database->getConnection();
    $tipo_sangre_query = $db->query("SELECT id_tipo_sangre, tipo_sangre FROM tiposdesangre");
    $tipo_sangre = $tipo_sangre_query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al conectar con la base de datos: " . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nacionalidad = $_POST['nacionalidad'];
    $tipo_sangre_id = $_POST['id_tipo_sangre'];
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $sexo = $_POST['sexo'];
    $correo = $_POST['correo'];

    $upload_dir = '../uploads/';
    $foto_cedula_frontal = $upload_dir . basename($_FILES['cedula_frente']['name']);
    $foto_cedula_trasera = $upload_dir . basename($_FILES['cedula_atras']['name']);
    $foto_persona_con_cedula = $upload_dir . basename($_FILES['persona_cedula']['name']);

    move_uploaded_file($_FILES['cedula_frente']['tmp_name'], $foto_cedula_frontal);
    move_uploaded_file($_FILES['cedula_atras']['tmp_name'], $foto_cedula_trasera);
    move_uploaded_file($_FILES['persona_cedula']['tmp_name'], $foto_persona_con_cedula);

    try {
        $query = "INSERT INTO PreRegistro 
            (cedula, nombre, apellido, fecha_nacimiento, genero, nacionalidad, tipo_sangre, correo, 
            foto_cedula_frontal, foto_cedula_trasera, foto_persona_con_cedula) 
            VALUES (:cedula, :nombre, :apellido, :fecha_nacimiento, :sexo, :nacionalidad, :tipo_sangre, :correo, 
            :foto_cedula_frontal, :foto_cedula_trasera, :foto_persona_con_cedula)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $stmt->bindParam(':sexo', $sexo);
        $stmt->bindParam(':nacionalidad', $nacionalidad);
        $stmt->bindParam(':tipo_sangre', $tipo_sangre_id);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':foto_cedula_frontal', $foto_cedula_frontal);
        $stmt->bindParam(':foto_cedula_trasera', $foto_cedula_trasera);
        $stmt->bindParam(':foto_persona_con_cedula', $foto_persona_con_cedula);
        $stmt->execute();

        // Enviar correo de confirmación con PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'talpihosclinic@gmail.com'; 
        $mail->Password = 'xtogjorlypqnpmuq'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('tu_correo@gmail.com', 'Hospital');
        $mail->addAddress($correo);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de Pre-registro';
        $mail->Body = "Hola $nombre,<br><br>Gracias por completar el formulario de pre-registro. Hemos recibido tus datos exitosamente.<br><br>Saludos,<br>";

        $mail->send();
        echo "Pre-registro enviado exitosamente y correo de confirmación enviado.";
    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Error al enviar el correo: {$mail->ErrorInfo}";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Pre-registro</title>
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f4f7f9;
            --text-color: #333;
            --border-color: #ccc;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: var(--secondary-color);
            color: var(--text-color);
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="file"] {
            display: none;
        }
        .file-upload {
            display: inline-block;
            padding: 10px 15px;
            background-color: var(--primary-color);
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .file-upload:hover {
            background-color: #3a7bc8;
        }
        .file-name {
            margin-top: 5px;
            font-size: 14px;
        }
        button {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 10px;
        }
        a{
            display: block;
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 10px; 
        }
        button:hover {
            background-color: #3a7bc8;
        }
        .return-button {
            background-color: #6c757d;
            text-align: center;
            text-decoration: none;
        }
        .return-button:hover {
            background-color: #5a6268;
        }
        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Formulario de Pre-registro</h1>
        <form action="#" method="POST" enctype="multipart/form-data">
        <div class="form-group">
                <label for="cedula">Cédula:</label>
                <input type="text" id="cedula" name="cedula" required>
            </div>
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" required>
            </div>
            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
            </div>
            <div class="form-group">
                <label for="sexo">Sexo:</label>
                <select id="sexo" name="sexo" required>
                    <option value="">Seleccione una opción</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="id_tipo_sangre">Tipo de Sangre:</label>
                <select id="id_tipo_sangre" name="id_tipo_sangre">
                    <option value="">Seleccione el tipo de sangre</option>
                    <?php foreach ($tipo_sangre as $tipos_sangre): ?>
            <option value="<?= $tipos_sangre['id_tipo_sangre']; ?>"><?= $tipos_sangre['tipo_sangre']; ?></option>
        <?php endforeach; ?>
               </select>
            </div>

    <div class="form-group">
        <label for="nacionalidad">Nacionalidad:</label>
        <select id="nacionalidad" name="nacionalidad" required>
            <option value="">Seleccione una nacionalidad</option>
            <option value="Panameña">Panameña</option>
            <option value="Panameño">Panameño</option>
        </select>
    </div>

            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            <div class="form-group">
                <label for="cedula_frente">Cédula (Frente):</label>
                <input type="file" id="cedula_frente" name="cedula_frente" accept="image/*" required>
                <label for="cedula_frente" class="file-upload">Subir imagen</label>
                <div class="file-name" id="cedula_frente_name"></div>
            </div>
            <div class="form-group">
                <label for="cedula_atras">Cédula (Atrás):</label>
                <input type="file" id="cedula_atras" name="cedula_atras" accept="image/*" required>
                <label for="cedula_atras" class="file-upload">Subir imagen</label>
                <div class="file-name" id="cedula_atras_name"></div>
            </div>
            <div class="form-group">
                <label for="persona_cedula">Persona con Cédula:</label>
                <input type="file" id="persona_cedula" name="persona_cedula" accept="image/*" required>
                <label for="persona_cedula" class="file-upload">Subir imagen</label>
                <div class="file-name" id="persona_cedula_name"></div>
            </div>
            <button type="submit">Enviar Pre-registro</button>
        </form>
        <a href="login.php" class="return-button button">Volver al Inicio de Sesión</a>
    </div>

    <script>
        document.querySelectorAll('input[type="file"]').forEach(fileInput => {
            fileInput.addEventListener('change', function(e) {
                const fileName = e.target.files[0].name;
                document.getElementById(`${e.target.id}_name`).textContent = fileName;
            });
        });

       
        document.querySelectorAll('.file-upload').forEach(uploadButton => {
            uploadButton.addEventListener('click', function(e) {
                e.preventDefault();
                const fileInput = document.getElementById(this.getAttribute('for'));
                fileInput.click();
            });
        });
    </script>
</body>
</html>
