
// Forward declaration
function render(root, playlist);

/*
 * Returns the basename ofthe given path.
 */
function basename(path) {
    return path.replace(/\\/g,'/').replace( /.*\//, '' );
}

