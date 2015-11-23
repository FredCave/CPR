module.exports = function ( grunt ) {

	// Configure tasks
	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),
		uglify: {
			dev: {
				beautify: true,
				src: "assets/js/*.js",
				dest: "js/scripts.min.js"
			}
		},
		watch: {
			js: {
				files: ["assets/js/*.js"],
				tasks: ["uglify:dev"]
			},
			grunt: { files: ['gruntfile.js'] }
		}
	});

	// Load the plugins
	grunt.loadNpmTasks("grunt-contrib-uglify");
	grunt.loadNpmTasks("grunt-contrib-watch");

	// Register tasks
	grunt.registerTask("default", ["uglify:dev"]);

}