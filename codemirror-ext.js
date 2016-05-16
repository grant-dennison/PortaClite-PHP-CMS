function extToCMMode(ext) {
    var map = {"groovy": "groovy",
    "ini": "properties",
    "properties": "properties",
    "css": "css",
    "scss": "css",
    "html": "htmlmixed",
    "htm": "htmlmixed",
    "shtm": "htmlmixed",
    "shtml": "htmlmixed",
    "xhtml": "htmlmixed",
    "cfm": "htmlmixed",
    "cfml": "htmlmixed",
    "cfc": "htmlmixed",
    "dhtml": "htmlmixed",
    "xht": "htmlmixed",
    "tpl": "htmlmixed",
    "twig": "htmlmixed",
    "hbs": "htmlmixed",
    "handlebars": "htmlmixed",
    "kit": "htmlmixed",
    "jsp": "htmlmixed",
    "aspx": "htmlmixed",
    "ascx": "htmlmixed",
    "asp": "htmlmixed",
    "master": "htmlmixed",
    "cshtml": "htmlmixed",
    "vbhtml": "htmlmixed",
    "ejs": "htmlembedded",
    "dust": "htmlembedded",
    "erb": "htmlembedded",
    "js": "javascript",
    "jsx": "javascript",
    "jsm": "javascript",
    "_js": "javascript",
    "vbs": "vbscript",
    "vb": "vb",
    "json": "javascript",
    "xml": "xml",
    "svg": "xml",
    "wxs": "xml",
    "wxl": "xml",
    "wsdl": "xml",
    "rss": "xml",
    "atom": "xml",
    "rdf": "xml",
    "xslt": "xml",
    "xsl": "xml",
    "xul": "xml",
    "xbl": "xml",
    "mathml": "xml",
    "config": "xml",
    "plist": "xml",
    "xaml": "xml",
    "php": "php",
    "php3": "php",
    "php4": "php",
    "php5": "php",
    "phtm": "php",
    "phtml": "php",
    "ctp": "php",
    "c": "clike",
    "h": "clike",
    "i": "clike",
    "cc": "clike",
    "cp": "clike",
    "cpp": "clike",
    "c++": "clike",
    "cxx": "clike",
    "hh": "clike",
    "hpp": "clike",
    "hxx": "clike",
    "h++": "clike",
    "ii": "clike",
    "ino": "clike",
    "cs": "clike",
    "asax": "clike",
    "ashx": "clike",
    "java": "clike",
    "scala": "clike",
    "sbt": "clike",
    "coffee": "coffeescript",
    "cf": "coffeescript",
    "cson": "coffeescript",
    "_coffee": "coffeescript",
    "clj": "clojure",
    "cljs": "clojure",
    "cljx": "clojure",
    "pl": "perl",
    "pm": "perl",
    "rb": "ruby",
    "ru": "ruby",
    "gemspec": "ruby",
    "rake": "ruby",
    "py": "python",
    "pyw": "python",
    "wsgi": "python",
    "sass": "sass",
    "lua": "lua",
    "sql": "sql",
    "diff": "diff",
    "patch": "diff",
    "md": "markdown",
    "markdown": "markdown",
    "mdown": "markdown",
    "mkdn": "markdown",
    "yaml": "yaml",
    "yml": "yaml",
    "hx": "haxe",
    "sh": "shell",
    "command": "shell",
    "bash": "shell"};


    if(ext in map) {
        return map[ext];
    }
    else {
        return "htmlmixed";
    }
}