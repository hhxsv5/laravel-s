const chokidar = require('chokidar');

const paths = JSON.parse(process.argv[2]);
const options = JSON.parse(process.argv[3]);

const watcher = chokidar.watch(paths, options);

watcher
    .on('add', () => console.log('file added ...'))
    .on('change', () => console.log('file changed ...'))
    .on('unlink', () => console.log('file deleted ...'))
    .on('unlinkDir', () => console.log('directory deleted ...'));
