<?php
/*
 * Game Constants
 */
const REPTILES = "reptiles";
const FELINES = "felines";
const RAPTORS = "raptors";
const URSIDS = "ursids";
const MARINES = "marines";

const NORMAL_BANNERS = [REPTILES, FELINES, RAPTORS, URSIDS, MARINES];
const RELIGIOUS = "religious";
const IMPERIAL = "imperial";
const ORDERS = [RELIGIOUS, IMPERIAL];

const ALL_BANNERS = [REPTILES, FELINES, RAPTORS, URSIDS, MARINES, RELIGIOUS, IMPERIAL];

//card type
const THRONE = 'throne';

const KING = 'king';
const QUEEN = 'queen';
const WITCH = 'witch';
const WARRIOR = 'warrior';
const TITAN = 'titan';
const NORMAL_CHARACTERS = [KING, QUEEN, WITCH, WARRIOR, TITAN];

const POPESS = 'popess';
const WARRIOR_MONK = 'warrior_monk';
const GAIA = 'gaia';
const OURANOS = 'ouranos';

const RELIGIOUS_CHARACTERS = [POPESS, WARRIOR_MONK, GAIA, OURANOS];

const COLONEL = 'colonel';
const GENERAL = 'general';
const CAPTAIN = 'captain';
const MARSHAL = 'marshal';

const IMPERIAL_CHARACTERS = [COLONEL, GENERAL, CAPTAIN, MARSHAL];



//Locations
const DISCARD = 'discard';
const DECK = 'deck';
const INFLUENCE = 'influence';
const CONCIL = 'concil';
const ALKANE = 'alkane';
const TITANS = 'titans';




/*
 * State constants
 */
const ST_GAME_SETUP = 1;

// const ST_PLAY = 2;



const ST_PRE_END_OF_GAME = 98;
const ST_END_GAME = 99;


/****
 * Cheat Module
 */

const OPTION_DEBUG = 103;
const OPTION_DEBUG_OFF = 0;
const OPTION_DEBUG_ON = 1;

/******************
 ****** STATS ******
 ******************/

// const STAT_FRMSLECTED_CRISTAL = 11;
// const STAT_WATER_SOURCES_POINTS = 12;
// const STAT_ANIMALS_POINTS = 13;
// const STAT_BIOMES_POINTS = 14;
// const STAT_SPORES_POINTS = 15;
// const STAT_ALIGNMENTS = 16;
// const STAT_END_STEP_ACTIVATIONS = 17;
// const STAT_END_ROUND_ACTIVATIONS = 18;

// const STAT_NAME_FRMSLECTED_CRISTAL = 'collectedCristal';
// const STAT_NAME_WATER_SOURCES_POINTS = 'waterSourcePoints';
// const STAT_NAME_ANIMALS_POINTS = 'animalsPoints';
// const STAT_NAME_BIOMES_POINTS = 'biomesPoints';
// const STAT_NAME_SPORES_POINTS = 'sporePoints';
// const STAT_NAME_ALIGNMENTS = 'alignments';
// const STAT_NAME_END_STEP_ACTIVATIONS = 'endStepActivations';
// const STAT_NAME_END_ROUND_ACTIVATIONS = 'endRoundActivations';

/*
*  ██████╗ ███████╗███╗   ██╗███████╗██████╗ ██╗ ██████╗███████╗
* ██╔════╝ ██╔════╝████╗  ██║██╔════╝██╔══██╗██║██╔════╝██╔════╝
* ██║  ███╗█████╗  ██╔██╗ ██║█████╗  ██████╔╝██║██║     ███████╗
* ██║   ██║██╔══╝  ██║╚██╗██║██╔══╝  ██╔══██╗██║██║     ╚════██║
* ╚██████╔╝███████╗██║ ╚████║███████╗██║  ██║██║╚██████╗███████║
*  ╚═════╝ ╚══════╝╚═╝  ╚═══╝╚══════╝╚═╝  ╚═╝╚═╝ ╚═════╝╚══════╝
*                                                               
*/


const GAME = "game";
const MULTI = "multipleactiveplayer";
const PRIVATESTATE = "private";
const END_TURN = 'endTurn';
const ACTIVE_PLAYER = "activeplayer";

const FLIPPED = 1;
const NOT_FLIPPED = 0;
const VISIBLE = 0;
