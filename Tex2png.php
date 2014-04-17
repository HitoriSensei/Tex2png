<?php

namespace Erk\Tex2png;

/**
 * Helper to generate PNG from LaTeX formula
 *
 * @author Grégoire Passault <g.passault@gmail.com> & Michał Kniotek
 */
class Tex2png
{
    /* TODO: do zainstalowania:
    sudo apt-get install texlive
    sudo apt-get install texlive-pstricks
    sudo apt-get install dvipng
    */

    /**
    * Where is the LaTex ?
    */
    const LATEX = "$(which latex)";
    
    /**
    * Where is the DVIPNG ?
    */
    const DVIPNG = "$(which dvipng)";

    /**
     * LaTeX packges
     */
    protected $packages = array('amssymb, amsmath', 'color', 'amsfonts', 'amssymb', 'pst-plot');
    
    /**
     * Temporary directory
     * This is needed to write temporary files needed for
     * generation
     */
    protected  $tmpDir = '/tmp';

    /**
     * Target file
     */
    protected  $file = null;

    /**
     * @var null Target file name
     */
    protected  $fileName = null;

    /**
     * Hash
     */
    protected  $hash;

    /**
     * LaTeX formula
     */
    protected  $formula;

    /**
     * Target density
     */
    protected  $density;

    /**
     * Error (if any)
     */
    protected  $error = null;

    public static function create($formula, $density = 155)
    {
        return new self($formula, $density);
    }

    protected function __construct($formula, $density = 155)
    {
        $datas = array(
            'formula' => $formula,
            'density' => $density,
        );

        $this->formula = $formula;
        $this->density = $density;
        $this->hash = sha1(serialize($datas));

        return $this;
    }

    /**
     * Generates the image
     */
    public function generate()
    {
        $tex2png = $this;

        // Generates the LaTeX file
        $tex2png->createFile();

        // Compile the latexFile
        $tex2png->latexFile();

        // Converts the DVI file to PNG
        $tex2png->dvi2png();

        $tex2png->clean();

        return $this;
    }

    /**
     * Create the LaTeX file
     */
    protected function createFile()
    {
        $tmpfile = $this->tmpDir . '/' . $this->hash . '.tex';

        $tex = '\documentclass[12pt]{article}'."\n";
        
        $tex .= '\usepackage[utf8]{inputenc}'."\n";

        // Packages
        foreach ($this->packages as $package) {
            $tex .= '\usepackage{' . $package . "}\n";
        }
        
        $tex .= '\begin{document}'."\n";
        $tex .= '\pagestyle{empty}'."\n";
        $tex .= '\begin{displaymath}'."\n";
        
        $tex .= $this->formula."\n";
        
        $tex .= '\end{displaymath}'."\n";
        $tex .= '\end{document}'."\n";
        if (file_put_contents($tmpfile, $tex) === false) {
            throw new \Exception('Failed to open target file');
        }
    }

    /**
     * Compiles the LaTeX to DVI
     */
    protected function latexFile()
    {
        $command = 'cd ' . $this->tmpDir . '; ' . self::LATEX . ' ' . $this->hash . '.tex '. ' 2> /dev/null 2>&1 1>' . $this->tmpDir . '/' .$this->hash . '.err';

        shell_exec($command);

        if (!file_exists($this->tmpDir . '/' . $this->hash . '.dvi')) {
            throw new \Exception('Unable to compile LaTeX formula (is latex installed? check syntax)');
        }
    }

    /**
     * Converts the DVI file to PNG
     */
    protected function dvi2png()
    {
        // XXX background: -bg 'rgb 0.5 0.5 0.5'
        $filename = $this->tmpDir . '/' . $this->hash . '.png';
        $command = self::DVIPNG . ' -q* -T tight -bg Transparent -D ' . $this->density . ' -o ' . $filename . ' ' . $this->tmpDir . '/' . $this->hash . '.dvi 2>&1';
        if (shell_exec($command) === null) {
            throw new \Exception('Unable to convert the DVI file to PNG (is dvipng installed?)');
        }
        $this->file = file_get_contents($filename);
        if($this->file === false){
            throw new \Exception('Unable to get PNG file (is tmp dir writable and readable?)');
        }
        $this->fileName = $filename;
    }

    /**
     * Cleaning
     */
    protected function clean()
    {
        @shell_exec('rm -f ' . $this->tmpDir . '/' . $this->hash . '.* 2>&1');
    }

    /**
     * Sets the temporary directory
     */
    public function setTempDirectory($directory)
    {
        $this->tmpDir = $directory;
    }

    /**
     * Returns the PNG file
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string returns file name
     */
    public function getFileName()
    {
        return $this->getFileName();
    }

    /**
     * The string representation is the cache file
     */
    public function __toString()
    {
        return $this->getFileName();
    }
}
