/**
 * Load the SVG file
 * @return { Promise<void> }
 */
async function getSVG() {
    const svgs = document.querySelectorAll('[data-svg-src$=".svg"]');

    for (const svg of svgs) {
        const url = svg.getAttribute('data-svg-src');

        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`No se puede obtener ${url}`);
        }

        const svgContent = await response.text();
        svg.innerHTML = sanitizeSVG(svgContent);
    }
}

/**
 * Sanear el contenido de un archivo SVG
 * @param { string } svg SVG a depurar
 * @returns 
 */
const sanitizeSVG = (svg) => {
    const sanitized = svg.replace(/\n/g, '');
    const jsRemoved = sanitized.replace(/<script.*?<\/script>/g, '');
    return jsRemoved;
}

getSVG();