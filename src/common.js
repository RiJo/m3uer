
// Forward declaration
function render(loader, root, playlist);

/*
 * Returns the basename ofthe given path.
 */
function basename(path) {
    return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

