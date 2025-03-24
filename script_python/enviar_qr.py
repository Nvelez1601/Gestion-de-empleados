import sys
import qrcode
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.base import MIMEBase
from email import encoders
import smtplib
import os

def generar_qr(nombre_invitado, correo_invitado):
    """
    Genera un código QR con el nombre y correo del invitado.
    """
    contenido_qr = f"Nombre: {nombre_invitado}\nCorreo: {correo_invitado}"
    qr = qrcode.QRCode(
        version=1,
        error_correction=qrcode.constants.ERROR_CORRECT_L,
        box_size=10,
        border=4,
    )
    qr.add_data(contenido_qr)
    qr.make(fit=True)

    # Guardar el QR como imagen
    nombre_archivo = f"qr_{nombre_invitado.replace(' ', '_')}.png"
    qr_img = qr.make_image(fill_color="black", back_color="white")
    qr_img.save(nombre_archivo)
    return nombre_archivo

def enviar_correo(receiver_email, subject, message, qr_file_path):
    """
    Envía un correo electrónico con un archivo adjunto (código QR).
    """
    email_sender = "nvelezcuauro@gmail.com"  # Reemplaza con tu email
    email_password = "rrwc ejnm jqme jepl"  # Usa una contraseña de aplicación
    smtp_server = "smtp.gmail.com"
    smtp_port = 587

    try:
        # Crear el mensaje de correo
        msg = MIMEMultipart()
        msg["From"] = email_sender
        msg["To"] = receiver_email
        msg["Subject"] = subject

        # Agregar el cuerpo del mensaje
        msg.attach(MIMEText(message, "html", "utf-8"))

        # Verificar si el archivo QR existe
        if not os.path.exists(qr_file_path):
            raise FileNotFoundError(f"El archivo QR {qr_file_path} no se encontró.")

        # Adjuntar el archivo QR
        with open(qr_file_path, "rb") as attachment:
            part = MIMEBase("application", "octet-stream")
            part.set_payload(attachment.read())
        encoders.encode_base64(part)
        part.add_header(
            "Content-Disposition",
            f"attachment; filename={os.path.basename(qr_file_path)}",
        )
        msg.attach(part)

        # Conectar al servidor SMTP y enviar el correo
        smtp_conn = smtplib.SMTP(smtp_server, smtp_port)
        smtp_conn.starttls()
        smtp_conn.login(email_sender, email_password)
        smtp_conn.sendmail(email_sender, receiver_email, msg.as_string())
        smtp_conn.quit()

        print(f"Correo enviado a {receiver_email} correctamente.")
    except (smtplib.SMTPException, FileNotFoundError, OSError) as e:
        print(f"Error al enviar el correo: {e}")

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Uso: python enviar_qr.py <correo> <nombre>")
        sys.exit(1)

    correo = sys.argv[1]
    nombre = sys.argv[2]

    # Generar el QR
    qr_path = generar_qr(nombre, correo)

    # Enviar el correo con el QR adjunto
    asunto = "Código QR para acceso de invitado"
    mensaje = f"<p>Hola {nombre},</p><p>Este es tu código QR para acceder a las instalaciones.</p>"
    enviar_correo(correo, asunto, mensaje, qr_path)

    # Eliminar el archivo QR después de enviarlo
    if os.path.exists(qr_path):
        os.remove(qr_path)