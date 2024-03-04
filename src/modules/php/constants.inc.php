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
const THRONE = 'Throne';

const KING = 'King';
const REINE = 'Reine';
const WITCH = 'Witch';
const WARRIOR = 'Warrior';
const TITAN = 'Titan';
const NORMAL_CHARACTERS = [KING, REINE, WITCH, WARRIOR, TITAN];

const POPESS = 'Popess';
const WARRIOR_MONK = 'Warrior Monk';
const GAIA = 'Gaia';
const OURANOS = 'Ouranos';

const RELIGIOUS_CHARACTERS = [POPESS, WARRIOR_MONK, GAIA, OURANOS];

const COLONEL = 'Colonel';
const GENERAL = 'General';
const CAPTAIN = 'Captain';
const MARSHAL = 'Marshal';

const IMPERIAL_CHARACTERS = [COLONEL, GENERAL, CAPTAIN, MARSHAL];



//Locations
const DISCARD = 'discard';
const DECK = 'deck';
const INFLUENCE = 'influence';
const COUNCIL = 'council';
const ALKANE = 'alkane';
const TITANS = 'titans';


const DIRECTIONS = [[-1, 0], [0, -1], [1, 0], [0, 1]];


/*
 * State constants
 */
const ST_GAME_SETUP = 1;

const ST_PLAY = 2;
const ST_NEXT_PLAYER = 3;



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
const END_GAME = 'endGame';
const ACTIVE_PLAYER = "activeplayer";

const FLIPPED = 1;
const NOT_FLIPPED = 0;
const VISIBLE = 0;
