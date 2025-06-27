from flask import Flask, request, jsonify
from flask_cors import CORS
import qrcode
import base64
import os
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.base import MIMEBase
from email import encoders
import smtplib

app = Flask(__name__)
CORS(app)  # Permitir solicitudes desde el frontend

# Ruta para recibir los datos y enviar el correo
@app.route('/enviar_qr', methods=['POST'])
def enviar_qr():
    try:
        data = request.json
        print("Datos recibidos:", data)  # Log para verificar los datos

        nombre = data.get('nombre')
        correo = data.get('correo')

        if not nombre or not correo:
            print("Faltan datos")  # Log para datos faltantes
            return jsonify({'error': 'Faltan datos'}), 400

        # Generar el código QR
        contenido_qr = f"{nombre}\n{correo}"
        print("Contenido del QR:", contenido_qr)  # Log para el contenido del QR

        qr = qrcode.QRCode(
            version=1,
            error_correction=qrcode.constants.ERROR_CORRECT_L,
            box_size=10,
            border=4,
        )
        qr.add_data(contenido_qr)
        qr.make(fit=True)

        qr_filename = f"qr_{nombre.replace(' ', '_')}.png"
        qr_img = qr.make_image(fill_color="black", back_color="white")
        qr_img.save(qr_filename)
        print("QR guardado como:", qr_filename)  # Log para el archivo QR

        # Configurar el correo
        email_sender = #CONFIGURAR AQUÍ TU CORREO ELECTRÓNICO
        email_password = #CONFIGURAR AQUÍ TU CONTRASEÑA DE CORREO ELECTRÓNICO
        if not email_sender or not email_password:
            print("Faltan credenciales de correo")  # Log para credenciales faltantes
            return jsonify({'error': 'Faltan credenciales de correo'}), 400
        smtp_server = "smtp.gmail.com"
        smtp_port = 587

        msg = MIMEMultipart()
        msg["From"] = email_sender
        msg["To"] = correo
        msg["Subject"] = "Código QR para acceso de invitado"
        msg.attach(MIMEText(f"Hola {nombre}, este es tu código QR para acceder.", "plain"))

        with open(qr_filename, "rb") as attachment:
            part = MIMEBase("application", "octet-stream")
            part.set_payload(attachment.read())
        encoders.encode_base64(part)
        part.add_header(
            "Content-Disposition",
            f"attachment; filename={qr_filename}",
        )
        msg.attach(part)

        print("Correo configurado correctamente")  # Log para configuración del correo

        # Enviar el correo
        smtp_conn = smtplib.SMTP(smtp_server, smtp_port)
        smtp_conn.starttls()
        smtp_conn.login(email_sender, email_password)
        smtp_conn.sendmail(email_sender, correo, msg.as_string())
        smtp_conn.quit()
        print("Correo enviado a:", correo)  # Log para el envío del correo

        # Eliminar el archivo QR después de enviarlo
        os.remove(qr_filename)
        print("Archivo QR eliminado")  # Log para la eliminación del archivo

        return jsonify({'message': 'Correo enviado correctamente'}), 200
    except Exception as e:
        print("Error:", str(e))  # Log para el error
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)