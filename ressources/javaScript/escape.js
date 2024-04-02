export {escapeHtml};
function escapeHtml(text) {
    // https://stackoverflow.com/questions/1787322/what-is-the-htmlspecialchars-equivalent-in-javascript
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}