$responder_a = $_GET['responder'] ?? 0;
if ($responder_a) {
    $original = obtenerMensaje($responder_a, $user_id);
    if ($original) {
        $destinatario_id = $original['remitente_id'];
        $asunto = "Re: " . $original['asunto'];
    }
}