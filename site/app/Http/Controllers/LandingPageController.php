<?php

namespace App\Http\Controllers;

class LandingPageController extends Controller
{
    public function cadeauNaissance()
    {
        return view('cadeau-naissance-sensoriel');
    }

    public function cadeauFemmeEnceinte()
    {
        return view('cadeau-femme-enceinte-sensoriel');
    }

    public function snoezlenAutisme()
    {
        return view('seance-snoezelen-autisme');
    }

    public function snoezlenAlzheimer()
    {
        return view('seance-snoezelen-alzheimer');
    }
}
