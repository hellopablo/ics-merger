# ICS Merger

This utility will find and merge groups of individual .ics files.


## Installation

### Using Homebrew

1. Tap Nails using `brew tap hellopablo/utilities`
2. Install using `brew install icsmerger`
3. Update as normal using `brew update && brew upgrade`

### Manually

1. Clone this repository
2. Compile the PHAR (See compiling, below)
3. Place the PHAR somewhere in your PATH
4. To update, simply `git pull origin master` and recompile the PHAR


## Usage

You have a new binary called `icsmerger`. The following commands are supported:


### `icsmerger merge`

This command will merge all discovered .ics files into a single file.

#### Options

The following options can be used to manipulate the command

- `--src` define the source file (i.e., where to look for files); defaults to the present working directory.
- `--dest` define where to write the merged .ics file; defaults to the present working directory.
- `--file` define the name of the merged .ics file; defaults to `merged.ics`


## Development

I welcome development on this tool, feel free to submit pull requests for fixes, updates and features.

Please also ensure that any tests are passing and documentation is updated.

### Compiling as a PHAR

Compiling the tool as a PHAR is made super easy using Box.

1. Install [Box](http://box-project.org)
2. Compile using `box build`
3. Output available at `dist/icsmerger.phar`
