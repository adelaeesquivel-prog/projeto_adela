<?php
namespace LeoBarbas\Helpers;

use Exception;
use InvalidArgumentException;
use Throwable;
use DateTimeImmutable;
use DateTimeZone;

class Utils
{
    private function __construct() {}

    public static function normalizarEntrada(mixed $entrada, string $tipo = 'texto'): mixed
    {
        switch ($tipo) {
            case 'email':
                if (!is_string($entrada)) {
                    return '';
                }

                return trim($entrada);
            case 'inteiro':
                if (!is_int($entrada) && !is_string($entrada)) {
                    return 0;
                }

                $inteiro = filter_var($entrada, FILTER_VALIDATE_INT);
                return $inteiro === false ? 0 : $inteiro;
            default:
                if (!is_string($entrada)) {
                    return '';
                }

                // Mantém o texto original; o escape é aplicado ao exibir o valor.
                return trim($entrada);
        }
    }

    public static function escaparHtml(int|string $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // Páginas que dependem do parâmetro id via url, usam esse método para verificar se o id é válido
    public static function verificarId(mixed $valor): void
    {
        if (
            (!is_int($valor) && !is_string($valor)) ||
            filter_var($valor, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]) === false
        ) {
            header("Location: index.php");
            exit;
        }
    }

    public static function dump(mixed $dados): void
    {
        echo "<pre>";
        var_dump($dados);
        echo "</pre>";
    }

    public static function registrarLog(Throwable $e): void
    {
        $pastaLogs = __DIR__ . '/../../logs';
        if (!is_dir($pastaLogs)) {
            mkdir($pastaLogs, 0777, true);
        }

        $dataHora = new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
        $mensagem = "[" . $dataHora->format("Y-m-d H:i:s") . "]\n" .
            "Arquivo: " . $e->getFile() . "\n" .
            "Linha: " . $e->getLine() . "\n" .
            "Mensagem: " . $e->getMessage() . "\n\n";

        file_put_contents($pastaLogs . '/erros.log', $mensagem, FILE_APPEND);
    }

    public static function formataData(string $data): string
    {
        return date("d/m/Y H:i", strtotime($data));
    }

    public static function codificarSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }

    public static function verificarSenha(string $senhaFormulario, string $senhaBanco): string
    {
        if (password_verify($senhaFormulario, $senhaBanco)) {
            return $senhaBanco;
        } else {
            return self::codificarSenha($senhaFormulario);
        }
    }

    public static function upload(mixed $arquivo): string
    {
        if (!is_array($arquivo)) {
            throw new InvalidArgumentException("Selecione uma imagem.");
        }

        $erroUpload = $arquivo["error"] ?? null;

        if ($erroUpload === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException("Selecione uma imagem.");
        }

        if ($erroUpload !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException("O envio da imagem falhou. Verifique o arquivo e tente novamente.");
        }

        if (!isset($arquivo["tmp_name"]) || !is_string($arquivo["tmp_name"]) || !is_uploaded_file($arquivo["tmp_name"])) {
            throw new InvalidArgumentException("O arquivo enviado é inválido.");
        }

        $tamanhoMaximo = 2 * 1024 * 1024; // 2MB

        if (!isset($arquivo["size"]) || !is_int($arquivo["size"])) {
            throw new InvalidArgumentException("Não foi possível identificar o tamanho da imagem.");
        }

        if ($arquivo["size"] > $tamanhoMaximo) {
            throw new InvalidArgumentException("A imagem deve ter no máximo 2 MB.");
        }

        $formatoDoArquivoEnviado = mime_content_type($arquivo["tmp_name"]);
        $extensoesPorFormato = [
            "image/jpeg" => ["jpg", "jpeg"],
            "image/gif" => ["gif"],
            "image/png" => ["png"],
            "image/webp" => ["webp"],
        ];

        if (!isset($extensoesPorFormato[$formatoDoArquivoEnviado])) {
            throw new InvalidArgumentException("Envie uma imagem JPEG, GIF, PNG ou WebP.");
        }

        if (!isset($arquivo["name"]) || !is_string($arquivo["name"])) {
            throw new InvalidArgumentException("O nome do arquivo é inválido.");
        }

        $nomeDoArquivo = basename($arquivo["name"]);
        $extensao = strtolower(pathinfo($nomeDoArquivo, PATHINFO_EXTENSION));

        if ($nomeDoArquivo === "" || mb_strlen($nomeDoArquivo) > 60) {
            throw new InvalidArgumentException("O nome da imagem deve ter até 60 caracteres.");
        }

        if (!in_array($extensao, $extensoesPorFormato[$formatoDoArquivoEnviado], true)) {
            throw new InvalidArgumentException("A extensão do arquivo não corresponde ao formato da imagem.");
        }

        $pastaImagens = __DIR__ . "/../../images";
        if (!is_dir($pastaImagens)) {
            mkdir($pastaImagens, 0777, true);
        }

        $caminhoDoArquivo = $pastaImagens . "/" . $nomeDoArquivo;

        if (!move_uploaded_file($arquivo["tmp_name"], $caminhoDoArquivo)) {
            throw new Exception("Não foi possível salvar a imagem enviada.");
        }

        return $nomeDoArquivo;
    }

    public static function alertaErro(string $mensagem): void
    {
        $mensagemSanitizada = filter_var($mensagem, FILTER_SANITIZE_SPECIAL_CHARS);
        die("<script>alert('$mensagemSanitizada'); history.back(); </script>");
    }
}