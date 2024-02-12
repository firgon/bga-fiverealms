let fiverealms_f = (data) => {
	return {
	  color: data[0],
	  value: data[1],
	};
  };
  
  /*
  * Game Constants
  */
  
  const YELLOW = 'yellow';
  const PURPLE = 'purple';
  const GREEN = 'green';
  const BLUE = 'blue';
  const GREY = 'grey';
  const RED = 'red';
  
  // prettier-ignore
  const CARDS_DATA = {
  1 : fiverealms_f([GREEN, 1]),
  2 : fiverealms_f([GREEN, 1]),
  3 : fiverealms_f([GREEN, 1]),
  4 : fiverealms_f([GREEN, 1]),
  5 : fiverealms_f([GREEN, 2]),
  6 : fiverealms_f([GREEN, 2]),
  7 : fiverealms_f([GREEN, 2]),
  8 : fiverealms_f([GREEN, 2]),
  9 : fiverealms_f([GREEN, 3]),
  10 : fiverealms_f([GREEN, 3]),
  11 : fiverealms_f([GREEN, 3]),
  12 : fiverealms_f([GREEN, 3]),
  13 : fiverealms_f([RED, 1]),
  14 : fiverealms_f([RED, 1]),
  15 : fiverealms_f([RED, 1]),
  16 : fiverealms_f([RED, 1]),
  17 : fiverealms_f([RED, 2]),
  18 : fiverealms_f([RED, 2]),
  19 : fiverealms_f([RED, 2]),
  20 : fiverealms_f([RED, 2]),
  21 : fiverealms_f([RED, 3]),
  22 : fiverealms_f([RED, 3]),
  23 : fiverealms_f([RED, 3]),
  24 : fiverealms_f([RED, 3]),
  25 : fiverealms_f([BLUE, 1]),
  26 : fiverealms_f([BLUE, 1]),
  27 : fiverealms_f([BLUE, 1]),
  28 : fiverealms_f([BLUE, 1]),
  29 : fiverealms_f([BLUE, 1]),
  30 : fiverealms_f([BLUE, 2]),
  31 : fiverealms_f([BLUE, 2]),
  32 : fiverealms_f([BLUE, 2]),
  33 : fiverealms_f([BLUE, 2]),
  34 : fiverealms_f([BLUE, 2]),
  35 : fiverealms_f([BLUE, 3]),
  36 : fiverealms_f([BLUE, 3]),
  37 : fiverealms_f([BLUE, 3]),
  38 : fiverealms_f([BLUE, 3]),
  39 : fiverealms_f([BLUE, 3]),
  40 : fiverealms_f([YELLOW, 1]),
  41 : fiverealms_f([YELLOW, 1]),
  42 : fiverealms_f([YELLOW, 1]),
  43 : fiverealms_f([YELLOW, 1]),
  44 : fiverealms_f([YELLOW, 1]),
  45 : fiverealms_f([YELLOW, 2]),
  46 : fiverealms_f([YELLOW, 2]),
  47 : fiverealms_f([YELLOW, 2]),
  48 : fiverealms_f([YELLOW, 2]),
  49 : fiverealms_f([YELLOW, 2]),
  50 : fiverealms_f([YELLOW, 3]),
  51 : fiverealms_f([YELLOW, 3]),
  52 : fiverealms_f([YELLOW, 3]),
  53 : fiverealms_f([YELLOW, 3]),
  54 : fiverealms_f([YELLOW, 3]),
  55 : fiverealms_f([PURPLE, 1]),
  56 : fiverealms_f([PURPLE, 1]),
  57 : fiverealms_f([PURPLE, 1]),
  58 : fiverealms_f([PURPLE, 1]),
  59 : fiverealms_f([PURPLE, 2]),
  60 : fiverealms_f([PURPLE, 2]),
  61 : fiverealms_f([PURPLE, 2]),
  62 : fiverealms_f([PURPLE, 2]),
  63 : fiverealms_f([PURPLE, 3]),
  64 : fiverealms_f([PURPLE, 3]),
  65 : fiverealms_f([PURPLE, 3]),
  66 : fiverealms_f([PURPLE, 3]),
  67 : fiverealms_f([GREY, 1]),
  68 : fiverealms_f([GREY, 1]),
  69 : fiverealms_f([GREY, 1]),
  70 : fiverealms_f([GREY, 1]),
  71 : fiverealms_f([GREY, 2]),
  72 : fiverealms_f([GREY, 2]),
  73 : fiverealms_f([GREY, 2]),
  74 : fiverealms_f([GREY, 2]),
  75 : fiverealms_f([GREY, 3]),
  76 : fiverealms_f([GREY, 3]),
  77 : fiverealms_f([GREY, 3]),
  78 : fiverealms_f([GREY, 3]),
  'back' : fiverealms_f(["", 1, ""])
  };