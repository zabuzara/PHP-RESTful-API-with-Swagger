<?php
/**
 * All input element file mime types
 */
abstract class MIME {
    const ALL = '';
    const AUDIO_AAC = ['audio/aac', '.aac'];
    const APPLICATION_X_ABIWORD = ['application/x-abiword', '.abw'];
    const APPLICATION_X_FREEARC = ['application/x-freearc', '.arc'];
    const IMAGE_AVIF = ['image/avif', '.avif'];
    const VIDEO_X_MSVIDEO = ['video/x-msvideo', '.avi'];
    const APPLICATION_VND_AMAZON_EBOOK = ['application/vnd.amazon.ebook', '.azw'];
    const APPLICATION_OCTET_STREAM = ['application/octet-stream', '.bin'];
    const IMAGE_BMP = ['image/bmp', '.bmp'];
    const APPLICATION_X_BZIP = ['application/x-bzip', '.bz'];
    const APPLICATION_X_BZIP2 = ['application/x-bzip2', '.bz2'];
    const APPLICATION_X_CDF = ['application/x-cdf', '.cda'];
    const APPLICATION_X_CSH = ['application/x-csh', '.csh'];
    const TEXT_CSS = ['text/css', '.css'];
    const TEXT_CSV = ['text/csv', '.csv'];
    const APPLICATION_MSWORD = ['application/msword', '.doc'];
    const APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_WORDPROCESSINGML_DOCUMENT = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', '.docx'];
    const APPLICATION_VND_MS_FONTOBJECT = ['application/vnd.ms-fontobject', '.eot'];
    const APPLICATION_EPUB_ZIP = ['application/epub+zip', '.epub'];
    const APPLICATION_GZIP = ['application/gzip', '.gz'];
    const IMAGE_GIF = ['image/gif', '.gif'];
    const TEXT_HTML = ['text/html', '.html', 'htm'];
    const IMAGE_VND_MICROSOFT_ICON = ['image/vnd.microsoft.icon', '.ico'];
    const TEXT_CALENDAR = ['text/calendar', '.ics'];
    const APPLICATION_JAVA_ARCHIVE = ['application/java-archive', '.jar'];
    const IMAGE_JPEG = ['image/jpeg', '.jpeg', '.jpg'];
    const TEXT_JAVASCRIPT = ['text/javascript', '.js', '.mjs'];
    const APPLICATION_JSON = ['application/json', '.json'];
    const APPLICATION_LD_JSON = ['application/ld+json', '.jsonld'];
    const AUDIO_MIDI = ['audio/midi', '.mid'];
    const AUDIO_X_MIDI = ['audio/x-midi', '.midi'];
    const AUDIO_MPEG = ['audio/mpeg', '.mp3'];
    const VIDEO_MP4 = ['video/mp4', '.mp4'];
    const VIDEO_MPEG = ['video/mpeg', '.mpeg'];
    const APPLICATION_VND_APPLE_INSTALLER_XML = ['application/vnd.apple.installer+xml', '.mpkg'];
    const APPLICATION_VND_OASIS_OPENDOCUMENT_PRESENTATION = ['application/vnd.oasis.opendocument.presentation', '.odp'];
    const APPLICATION_VND_OASIS_OPENDOCUMENT_SPREADSHEET = ['application/vnd.oasis.opendocument.spreadsheet', '.ods'];
    const APPLICATION_VND_OASIS_OPENDOCUMENT_TEXT = ['application/vnd.oasis.opendocument.text', '.odt'];
    const AUDIO_OGG = ['audio/ogg', '.oga'];
    const VIDEO_OGG = ['video/ogg', '.ogv'];
    const APPLICATION_OGG = ['application/ogg', '.ogx'];
    const AUDIO_OPUS = ['audio/opus', '.opus'];
    const FONT_OTF = ['font/otf', '.otf'];
    const IMAGE_PNG = ['image/png', '.png'];
    const APPLICATION_PDF = ['application/pdf', '.pdf'];
    const APPLICATION_X_HTTPD_PHP = ['application/x-httpd-php', '.php'];
    const APPLICATION_VND_MS_POWERPOINT = ['application/vnd.ms-powerpoint', '.ppt'];
    const APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_PRESENTATIONML_PRESENTATION = ['application/vnd.openxmlformats-officedocument.presentationml.presentation', '.pptx'];
    const APPLICATION_VND_RAR = ['application/vnd.rar', '.rar'];
    const APPLICATION_RTF = ['application/rtf', '.rtf'];
    const APPLICATION_X_SH = ['application/x-sh', '.sh'];
    const IMAGE_SVG_XML = ['image/svg+xml', '.svg'];
    const APPLICATION_X_TAR = ['application/x-tar', '.tar'];
    const IMAGE_TIFF = ['image/tiff', '.tif', '.tiff'];
    const VIDEO_MP2T = ['video/mp2t', '.ts'];
    const FONT_TTF = ['font/ttf', '.ttf'];
    const TEXT_PLAIN = ['text/plain', '.txt'];
    const APPLICATION_VND_VISIO = ['application/vnd.visio', '.vsd'];
    const AUDIO_WAV = ['audio/wav', '.wav'];
    const AUDIO_WEBM = ['audio/webm', '.weba'];
    const VIDEO_WEBM = ['video/webm', '.webm'];
    const IMAGE_WEBP = ['image/webp', '.webp'];
    const FONT_WOFF = ['font/woff', '.woff'];
    const FONT_WOFF2 = ['font/woff2', '.woff2'];
    const APPLICATION_XHTML_XML = ['application/xhtml+xml', '.xhtml'];
    const APPLICATION_VND_MS_EXCEL = ['application/vnd.ms-excel', '.xls'];
    const APPLICATION_VND_OPENXMLFORMATS_OFFICEDOCUMENT_SPREADSHEETML_SHEET = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '.xlsx'];
    const APPLICATION_XML = ['application/xml', '.xml'];
    const TEXT_XML = ['text/xml', '.xml'];
    const APPLICATION_ATOM_XML = ['application/atom+xml', '.xml'];
    const APPLICATION_VND_MOZILLA_XUL_XML = ['application/vnd.mozilla.xul+xml', '.xul'];
    const APPLICATION_ZIP = ['application/zip', '.zip'];
    const VIDEO_3GPP = ['video/3gpp', '.3gp'];
    const AUDIO_3GPP = ['audio/3gpp', '.3gp'];
    const VIDEO_3GPP2 = ['video/3gpp2', '.3g2'];
    const AUDIO_3GPP2 = ['audio/3gpp2', '.3gp2'];
    const APPLICATION_X_7Z_COMPRESSED = ['application/x-7z-compressed', '.zip'];


    static public function get_extension (string $content_type) {
        $reflection = new ReflectionClass(self::class);
        $content_type = trim($content_type);
        foreach ($reflection->getConstants(ReflectionClassConstant::IS_PUBLIC) as $constant) {
            if ($content_type === trim($constant[0]))
                return trim($constant[1]);
        }
    }
}