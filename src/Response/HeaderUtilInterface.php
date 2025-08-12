<?php
namespace Concept\SimpleHttp\Response;

/**
 * Interface HeaderUtilInterface
 *
 * This interface defines constants for common HTTP headers and content types.
 * It can be used to standardize header names and content types across the application.
 */
interface HeaderUtilInterface
{
    const HEADER_CONTENT_TYPE = 'Content-Type';
    const HEADER_CONTENT_LENGTH = 'Content-Length';
    const HEADER_CACHE_CONTROL = 'Cache-Control';
    const HEADER_DATE = 'Date';
    const HEADER_LAST_MODIFIED = 'Last-Modified';
    const HEADER_ETAG = 'ETag';
    const HEADER_LOCATION = 'Location';
    const HEADER_ALLOW = 'Allow';
    const HEADER_ACCEPT_RANGES = 'Accept-Ranges';
    const HEADER_X_FRAME_OPTIONS = 'X-Frame-Options';
    const HEADER_X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    const HEADER_X_XSS_PROTECTION = 'X-XSS-Protection';

    const CONTENT_TYPE_HTML = 'text/html';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_XML = 'application/xml';
    const CONTENT_TYPE_TEXT = 'text/plain';
    const CONTENT_TYPE_CSS = 'text/css';
    const CONTENT_TYPE_JAVASCRIPT = 'application/javascript';
}