/**
 * Format a date string, timestamp, or Date object into 'YYYY-MM-DD' or 'YYYY-MM-DD HH:mm:ss'.
 * - If time is '00:00:00' (or missing), it formats as 'YYYY-MM-DD' (e.g. 2026-09-25).
 * - If time is present and non-zero, it formats as 'YYYY-MM-DD HH:mm:ss' (e.g. 2026-09-25 10:00:00).
 * - If forceDateOnly is true, it always returns 'YYYY-MM-DD' (ideal for <input type="date">).
 */
export function formatDate(
    value: string | number | Date | null | undefined,
    forceDateOnly?: boolean
): string {
    if (!value) return '';

    if (typeof value === 'string') {
        const trimmed = value.trim();
        if (!trimmed) return '';

        // Match YYYY-MM-DD
        if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) {
            return trimmed;
        }

        // Match YYYY-MM-DD HH:mm:ss or YYYY-MM-DD HH:mm
        const dateTimeMatch = trimmed.match(/^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2}(?::\d{2})?)$/);
        if (dateTimeMatch) {
            if (forceDateOnly) return dateTimeMatch[1];
            const timePart = dateTimeMatch[2].length === 5 ? `${dateTimeMatch[2]}:00` : dateTimeMatch[2];
            return timePart === '00:00:00' ? dateTimeMatch[1] : `${dateTimeMatch[1]} ${timePart}`;
        }

        // Match ISO 8601 like 2026-09-25T00:00:00.000000Z or 2026-09-25T10:00:00Z
        const isoMatch = trimmed.match(/^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2}:\d{2})/);
        if (isoMatch) {
            if (forceDateOnly) return isoMatch[1];
            return isoMatch[2] === '00:00:00' ? isoMatch[1] : `${isoMatch[1]} ${isoMatch[2]}`;
        }
    }

    const d = new Date(value);
    if (isNaN(d.getTime())) return String(value);

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const dateStr = `${year}-${month}-${day}`;

    if (forceDateOnly) return dateStr;

    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const seconds = String(d.getSeconds()).padStart(2, '0');

    if (hours === '00' && minutes === '00' && seconds === '00') {
        return dateStr;
    }

    return `${dateStr} ${hours}:${minutes}:${seconds}`;
}
