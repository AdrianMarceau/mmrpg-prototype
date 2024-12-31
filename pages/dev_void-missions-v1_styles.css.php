
/* -- PARENT PAGE STYLES -- */

#window .page .subbody .legend {
    display: block;
    margin: 0 auto;
    font-size: 80%;
}
#window .page .body .subbody > .subbody {
    margin-bottom: 20px;
}
#window .page .body .subbody #void-recipe {
    margin-top: 20px;
    margin-bottom: 20px;
}

/* -- VOID RECIPE CALCULATOR -- */

#void-recipe {
    display: block;
    box-sizing: border-box;
    position: relative;
    z-index: 1;
    width: 600px;
    height: auto;
    background-color: #262626;
    border: 1px solid #1A1A1A;
    border-radius: 6px;
    padding: 6px;
    margin: 0 auto;
    font-size: 13px;
    line-height: 16px;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.25);
    background-image: linear-gradient(0deg, #331f28, #2b2546);
    overflow: hidden;
}
#void-recipe:before {
    content: "";
    display: block;
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 2;
    border-radius: 6px;
    pointer-events: none;
    background-color: inherit;
    background-image: url(/images/assets/website-texture_circuit-board-2k23_full-white.png);
    background-position: center top;
    background-repeat: repeat;
    opacity: 0.9;
    mix-blend-mode: luminosity;
}
#void-recipe:after {
    content: "";
    display: block;
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
    border-radius: 6px;
    pointer-events: none;
    background-color: transparent;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.8), rgba(255, 255, 255, 0.1));
    opacity: 0.3;
}

/* -- BASIC STRUCTURES -- */

#void-recipe .deck,
#void-recipe .title,
#void-recipe .creation,
#void-recipe .selection,
#void-recipe .effects,
#void-recipe .palette,
#void-recipe .void,
#void-recipe .mission,
#void-recipe .wrapper,
#void-recipe .group,
#void-recipe .item-list,
#void-recipe .target-list,
#void-recipe .mission-details,
#void-recipe .void-powers,
#void-recipe .flow {
    display: block;
    box-sizing: border-box;
    width: auto;
    height: auto;
    text-align: center;
    position: relative;
    margin: 0 auto;
}
#void-recipe .deck:after,
#void-recipe .title:after,
#void-recipe .creation:after,
#void-recipe .selection:after,
#void-recipe .effects:after,
#void-recipe .palette:after,
#void-recipe .void:after,
#void-recipe .mission:after,
#void-recipe .wrapper:after,
#void-recipe .group:after,
#void-recipe .item-list:after,
#void-recipe .target-list:after,
#void-recipe .mission-details:after,
#void-recipe .void-powers:after,
#void-recipe .flow:after {
    content: "";
    display: block;
    clear: both;
}


/* -- UPPER / LOWER DECK -- */

#void-recipe .upper-deck,
#void-recipe .lower-deck {
    display: block;
    position: relative;
    z-index: 4;
    width: auto;
    margin: 0 auto;
    text-align: center;
    color: #ffffff;
    text-shadow: 1px 1px 0 rgb(0 0 0);
}
#void-recipe .upper-deck {
    border: 0 none transparent;
    background-color: transparent;
    background-image: none;
    min-height: 215px;
    padding: 3px 3px 46px;
    margin-bottom: 0;
    border-radius: 0;
    /* background-color: red; */
}
#void-recipe .lower-deck {
    border: 0 none transparent;
    background-color: transparent;
}

#void-recipe .upper-deck .loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.6;
    z-index: 999;
    pointer-events: none;
}

/* -- EFFECTS / BLACK HOLE -- */

#void-recipe .effects {
    display: block;
    box-sizing: border-box;
    position: absolute;
    z-index: 3;
    margin: 0 auto;
    width: auto;
    height: auto;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    overflow: hidden;
    pointer-events: none;
    background-color: transparent;
    mix-blend-mode: overlay;
    /* background-color: magenta; */
}
#void-recipe .effects > .black-hole {
    display: block;
    box-sizing: border-box;
    position: absolute;
    z-index: 1;
    margin: 0 auto;
    width: 100px;
    height: 100px;
    overflow: visible;
    top: 90px;
    left: 50%;
    right: auto;
    bottom: auto;
    transform: translate(-50%, 0) scale(2.0);
    background-color: transparent;
    background-image: none;
    opacity: 0.8;
    /* background-color: lime; */
}
#void-recipe .effects .black-hole .layer {
    content: "";
    display: block;
    box-sizing: border-box;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    position: absolute;
    transform-origin: center center;
    transform: rotate(0deg);
    background-color: transparent;
    background-image: url(/images/assets/void-cauldron_black-hole_2k24.png?2024-12-06);
    background-size: 100% 100%;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center center;
    filter: opacity(1.0) invert(0.0);
    z-index: 1;
    animation: none;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
    /* outline: 1px dotted magenta;  */
}

#void-recipe .effects .black-hole .layer.first { width: 100px; height: 100px; top: 0; left: 0; z-index: 3; }
#void-recipe .effects .black-hole .layer.second { width: 200px; height: 200px; top: -50px; left: -50px; z-index: 2; }
#void-recipe .effects .black-hole .layer.third { width: 300px; height: 300px; top: -100px; left: -100px; z-index: 1; }
#void-recipe .effects .black-hole .layer.fourth { width: 400px; height: 400px; top: -150px; left: -150px; z-index: 1; }

#void-recipe .effects .black-hole .layer.first { animation: rotate-clockwise 100s linear infinite; animation-delay: 0.8s; }
#void-recipe .effects .black-hole .layer.second { animation: rotate-clockwise 120s linear infinite; animation-delay: 0.6s; }
#void-recipe .effects .black-hole .layer.third { animation: rotate-clockwise 140s linear infinite; animation-delay: 0.4s; }
#void-recipe .effects .black-hole .layer.fourth { animation: rotate-clockwise 160s linear infinite; animation-delay: 0.2s; }

#void-recipe .effects .black-hole .layer.first { filter: opacity(0.3) invert(0.0); }
#void-recipe .effects .black-hole .layer.second { filter: opacity(0.5) invert(1.0) drop-shadow(2px 4px 6px black); }
#void-recipe .effects .black-hole .layer.third { filter: opacity(0.3) invert(0.0); }
#void-recipe .effects .black-hole .layer.fourth { filter: opacity(0.4) invert(1.0) drop-shadow(2px 4px 6px black); }

@keyframes rotate-clockwise {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
@keyframes rotate-counterclockwise {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(-360deg); }
}


/* -- TITLE / HEADER AREA -- */

#void-recipe .title {
    display: block;
    z-index: 10;
    width: 300px;
    margin: 0 auto;
    text-align: center;
    padding: 3px 12px;
    border: 0 none transparent;
    background-color: transparent;
    color: #cfcdd5;
}
#void-recipe .title .main,
#void-recipe .title .sub {
    display: block;
    margin: 0 auto;
    border-width: 0 10px;
    border-style: solid;
    border-color: transparent;
}
#void-recipe .title .main {
    font-size: 14px;
    line-height: 18px;
    text-transform: uppercase;
    padding-bottom: 3px;
    border-bottom: 1px solid #1c1a24;
}
#void-recipe .title .sub {
    font-size: 11px;
    line-height: 15px;
    color: #a49ad6;
    font-style: normal;
    padding-top: 1px;
    border-top: 1px solid #2a2636;
}

/* -- VOID CREATION -- */

#void-recipe .creation {
    position: absolute;
    z-index: 20;
    margin: 0 auto;
    top: 59px;
    left: 50%;
    transform: translateX(-50%);
    right: auto;
    width: 540px;
    height: 110px;
    border-radius: 3px;
    box-shadow: none;
    overflow: visible;
    border: 1px solid #1b1825;
    background-color: #312c3f;
}
#void-recipe .creation > div {
    display: block;
    margin: 0 auto;
    width: auto;
    height: auto;
    min-width: 180px;
    min-height: 50px;
    overflow: visible;
    border: 0 none transparent;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1;
}
#void-recipe .creation:after {
    content: "";
    display: block;
    position: absolute;
    z-index: 2;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    border: 0 none transparent;
    border-radius: 0 0 6px 6px;
    box-shadow: inset 4px 4px 8px rgba(0, 0, 0, 0.2);
    pointer-events: none;
}

/* -- CREATION >> MISSION DETAILS -- */

#void-recipe .creation .mission-details {
    position: absolute;
    width: auto;
    /* height: 120px; */
    height: 0;
    min-height: 0;
    top: -63px;
    left: -22px;
    right: -26px;
    z-index: 3;
    /* background-color: magenta; */
}
#void-recipe .creation .mission-details:hover {
    z-index: 99;
}

/* -- CREATION >> BATTLE FIELD (Background/Foreground) -- */

#void-recipe .creation .battle-field {
    overflow: hidden;
    border: 0 none transparent;
    position: absolute;
    pointer-events: none;
    z-index: 1;
    border-radius: 3px;
}
#void-recipe .creation .battle-field .sprite.background,
#void-recipe .creation .battle-field .sprite.foreground {
    position: absolute;
    margin: 0 auto;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    width: auto;
    height: auto;
    background-repeat: repeat;
    background-position: center center;
    background-size: auto 100%;
}
#void-recipe .creation .battle-field .sprite.background {
    z-index: 1;
}
#void-recipe .creation .battle-field .sprite.foreground {
    background-repeat: repeat-x;
    background-position: center 10px;
    z-index: 2;
}
#void-recipe .creation .battle-field .sprite.hazy-filter {
    filter: sepia(1) saturate(4) brightness(0.3) hue-rotate(222deg);
}
#void-recipe .creation .battle-field .sprite.background.memory-filter {
    filter: saturate(3) brightness(0.8) blur(1px);
}
#void-recipe .creation .battle-field .sprite.foreground.memory-filter {
    filter: saturate(2) brightness(0.9) blur(0px);
}
#void-recipe .creation .battle-field:after {
    content: "";
    display: block;
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 4;
    pointer-events: none;
    background-color: #292834;
    background-color: rgb(41, 40, 52, 0.8);
}

/* -- CREATION >> VOID POWERS (General) -- */

#void-recipe .mission-details .void-powers {
    display: block;
    width: auto;
    height: auto;
    position: absolute;
}
#void-recipe .mission-details .void-powers .power {
    display: block;
    white-space: nowrap;
    vertical-align: top;
    text-align: center;
    height: auto;
    width: auto;
    padding: 0;
    margin: 0;
    float: none;
    clear: both;
    border: 0 none transparent;
    background-color: transparent;
    font-size: inherit;
    line-height: 1;
    color: #ffffff;
    text-shadow: 1px 1px 0px rgba(0, 0, 0, 0.4);
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.3);
}
#void-recipe .mission-details .void-powers .power.type {
    padding: 1px 6px 3px;
    border: 1px solid #696969;
    background-color: #2d2c3a;
    border-radius: 3px;
    font-size: 14px;
    cursor: pointer;
}
#void-recipe .mission-details .void-powers .power:first-child {
    margin-top: 0;
    margin-left: 0;
}
#void-recipe .mission-details .void-powers .power:last-child {
    margin-right: 0;
    margin-bottom: 0;
}
#void-recipe .mission-details .void-powers.ltr .power,
#void-recipe .mission-details .void-powers.rtl .power {
    float: left;
    clear: none;
}
#void-recipe .mission-details .void-powers.ltr .power {
    margin-right: 3px;
}
#void-recipe .mission-details .void-powers.rtl .power {
    margin-left: 3px;
}
#void-recipe .mission-details .void-powers .power > span  {
    display: inline-block;
    vertical-align: middle;
    text-align: center;
    margin: 0 auto;
}
#void-recipe .mission-details .void-powers .power,
#void-recipe .mission-details .void-powers .power > span {
    display: block;
    box-sizing: border-box;
    vertical-align: middle;
    margin: 0 auto;
    padding: 0;
}
#void-recipe .mission-details .void-powers .power > span {
    display: inline-block;
    margin: 0 0 0 auto;
    vertical-align: middle;
    height: auto;
    width: auto;
    margin: 0;
    padding: 0;
    border: 0 none transparent;
    color: #ffffff;
    font-size: 12px;
    border-radius: 0;
    text-align: center;
}
#void-recipe .mission-details .void-powers .power > span > data + sub {
    font-size: 10px;
    margin-left: 4px;
    color: rgba(255, 255, 255, 0.6);
    text-shadow: 1px 1px 0px rgba(0, 0, 0, 0.1);
}
#void-recipe .mission-details .void-powers.ltr .power > span {
    margin-right: 6px;
    padding-right: 6px;
    border-right: 1px solid rgba(0, 0, 0, 0.1);
}
#void-recipe .mission-details .void-powers.rtl .power > span {
    margin-left: 6px;
    padding-left: 6px;
    border-left: 1px solid rgba(0, 0, 0, 0.1);
}
#void-recipe .mission-details .void-powers .power > span > strong {
    font-weight: bold;
}
#void-recipe .mission-details .void-powers .power > span > code {
    font-size: 10px;
    font-weight: bold;
    position: relative;
    top: -1px;
}
#void-recipe .mission-details .void-powers.ltr .power > span:last-child,
#void-recipe .mission-details .void-powers.rtl .power > span:first-child {
    margin: 0;
    padding: 0;
    border: 0 none transparent;
}
#void-recipe .mission-details .void-powers .power > span.blur {
    opacity: 1;
    max-width: 100px;
    overflow: visible;
    text-overflow: ellipsis;
    transition: max-width 0.6s, opacity 0.6s, margin 0.6s, padding 0.6s, border 0.6s;
}
#void-recipe .mission-details .void-powers .power > span.blur:before,
#void-recipe .mission-details .void-powers .power > span.blur:after {
    content: " ";
    display: inline-block;
    width: 2px;
    margin: 0;
}
#void-recipe .mission-details .void-powers .power > span.blur > strong,
#void-recipe .mission-details .void-powers .power > span.blur > data {
    font-size: 10px;
    font-weight: normal;
    position: relative;
    top: -1px;
}
#void-recipe .mission-details .void-powers.bgi .power:not(:hover) > span.blur,
#void-recipe .mission-details .void-powers.bgo .power:not(:hover) > span.blur:not(:first-child) {
    opacity: 0;
    max-width: 0;
    overflow: hidden;
    margin: 0;
    padding: 0;
    border: 0 none transparent;
}

#void-recipe .mission-details .void-powers .power > span > i {
    position: relative;
    text-shadow: 1px 1px 0px rgba(0, 0, 0, 0.6);
}
#void-recipe .mission-details .void-powers .power > span > i + i {
    margin-left: -8%;
}
#void-recipe .mission-details .void-powers .power > span > i:nth-child(1) { z-index: 10; }
#void-recipe .mission-details .void-powers .power > span > i:nth-child(2) { z-index: 9; }
#void-recipe .mission-details .void-powers .power > span > i:nth-child(3) { z-index: 8; }
#void-recipe .mission-details .void-powers .power > span > i:nth-child(4) { z-index: 7; }
#void-recipe .mission-details .void-powers .power > span > i:nth-child(5) { z-index: 6; }
#void-recipe .mission-details .void-powers .power > span > i:nth-child(6) { z-index: 5; }
#void-recipe .mission-details .void-powers .power > span > i:nth-child(7) { z-index: 4; }
#void-recipe .mission-details .void-powers .power > span > i:nth-child(8) { z-index: 3; }
#void-recipe .mission-details .void-powers .power > span > i:nth-child(9) { z-index: 2; }
#void-recipe .mission-details .void-powers .power > span > i:nth-child(10) { z-index: 1; }

/* -- CREATION >> VOID POWERS (Base Powers [Quanta + Spread]) -- */

#void-recipe .creation .mission-details .base-powers {
    z-index: 10;
    left: 6px;
    top: 6px;
}
#void-recipe .creation .mission-details .base-powers .power {
    font-size: 14px;
}

/* -- CREATION >> VOID POWERS (Delta Power [Num Parts]) -- */

#void-recipe .creation .mission-details .delta-power {
    z-index: 9;
    left: 128px;
    top: 10px;
}
#void-recipe .creation .mission-details .delta-power .power {
    font-size: 9px;
    line-height: 1;
    box-shadow: none;
}
#void-recipe .creation .mission-details .delta-power .power .icon {
    font-size: inherit;
    line-height: 1;
    width: 13px;
    height: 13px;
    padding: 0;
    border: 0 none transparent !important;
    border-radius: 50%;
    text-shadow: 1px 1px 0 rgba(0, 0, 0, 1);
    box-shadow: -1px 1px 0 rgba(0, 0, 0, 0.3);
    position: relative;
    transform: scale(1.0);
    transition: transform 0.2s;
    cursor: default;
}
#void-recipe .creation .mission-details .delta-power .power:hover .icon {
    transform: scale(0.8);
}
#void-recipe .creation .mission-details .delta-power .power .icon i {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 8px;
    line-height: 1;
    font-weight: bold;
}
#void-recipe .creation .mission-details .delta-power .power .value,
#void-recipe .creation .mission-details .delta-power .power .name {
    font-size: inherit;
    border: 0 none transparent;
    padding: 0;
    margin-right: 3px;
}
#void-recipe .creation .mission-details .delta-power .power .name {
    display: block;
    text-align: left;
    font-size: 8px;
    text-transform: lowercase;
    transform: translateY(4px);
}

/* -- CREATION >> VOID POWERS (Rank Powers [Level + Forte]) -- */

#void-recipe .creation .mission-details .rank-powers {
    z-index: 10;
    top: 6px;
    right: 6px;
}
#void-recipe .creation .mission-details .rank-powers .power {
    font-size: 14px;
}

/* -- CREATION >> VOID POWERS (Sort Powers [Types + Stats]) -- */

#void-recipe .creation .mission-details .sort-powers {
    z-index: 10;
    left: 6px;
    top: 32px;
}
#void-recipe .creation .mission-details .sort-powers + .sort-powers {
    top: 56px;
}
#void-recipe .creation .mission-details .sort-powers .label,
#void-recipe .creation .mission-details .sort-powers .flow {
    display: block;
    position: relative;
    vertical-align: top;
    float: left;
    margin: 0 3px 0 0;
}
#void-recipe .creation .mission-details .sort-powers .label {
    padding: 3px;
    color: #ffffff;
    min-width: 1em;
    min-height: 1em;
    font-size: 12px;
    line-height: 12px;
    text-align: center;
    border-radius: 6px;
    background-color: #2d2c3a;
    text-shadow: 1px 1px 0px rgba(0, 0, 0, 0.4);
    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.3);
    border: 1px solid #3d4166 !important;
}
#void-recipe .creation .mission-details .void-powers.sort-powers .label .icon {
    font-size: 12px;
    padding: 0;
    margin: 0;
}
#void-recipe .creation .mission-details .void-powers.sort-powers .label .icon:first-child {
    margin-right: 3px;
}
#void-recipe .creation .mission-details .void-powers.sort-powers .label .icon:last-child {
    padding-left: 3px;
}
#void-recipe .creation .mission-details .void-powers.sort-powers .label .name {
    margin-right: 3px;
}
#void-recipe .creation .mission-details .void-powers.sort-powers .flow {
    display: block;
    overflow: visible;
    box-sizing: border-box;
    width: auto;
    /* min-width: 300px; */
    min-width: 10px;
    max-width: none;
    height: 24px;
}
#void-recipe .creation .mission-details .sort-powers .flow .power {
    min-width: 10px;
    font-size: 13px;
    padding: 1px 3px 5px;
    margin: 0 3px 3px 0;
    border-radius: 2px;
    border: 1px solid transparent;
    border-color: rgba(0, 0, 0, 0.1) !important;
    border-left-color: rgba(0, 0, 0, 0.2) !important;
    border-right-color: rgba(255, 255, 255, 0.05) !important;
}
#void-recipe .creation .mission-details .sort-powers .flow .power:first-child {
    border-top-left-radius: 6px;
    border-bottom-left-radius: 6px;
}
#void-recipe .creation .mission-details .sort-powers .flow .power:last-child {
    border-top-right-radius: 6px;
    border-bottom-right-radius: 6px;
}
#void-recipe .creation .mission-details .sort-powers .power.plus:before,
#void-recipe .creation .mission-details .sort-powers .power.minus:after {
    content: " ";
    display: inline-block;
    width: 3px;
}
#void-recipe .creation .mission-details .sort-powers .power.plus {
    box-shadow: inset 2px 0 0 rgb(255, 255, 255, 0.2);
}
#void-recipe .creation .mission-details .sort-powers .power.minus {
    border-style: dotted;
    box-shadow: inset -2px 0 0 rgb(0, 0, 0, 0.2);
    filter: brightness(0.8) saturate(1.6);
}
#void-recipe .creation .mission-details .sort-powers .power > span {
    font-size: 9px;
}
#void-recipe .creation .mission-details .sort-powers .power:nth-child(5n) + .power {
    clear: left;
}
#void-recipe .creation .mission-details .sort-powers .power > span.blur > strong,
#void-recipe .creation .mission-details .sort-powers .power > span.blur > data {
    font-size: inherit;
    top: auto;
}

/* -- CREATION >> VOID POWERS (STAT POWERS) -- */

#void-recipe .creation .mission-details .stat-powers {
    z-index: 10;
    top: 34px;
    right: 6px;
}
#void-recipe .creation .mission-details .stat-powers .power {
    float: right;
    clear: both;
    margin-bottom: 3px;
}
#void-recipe .creation .mission-details .stat-powers .power .icon {
    min-width: 1em;
}
#void-recipe .creation .mission-details .stat-powers .power .arrows {
    margin: 0;
    padding: 0;
    border: 0 none transparent;
}
#void-recipe .creation .mission-details .stat-powers .power .arrow {
    display: inline-block;
    position: relative;
    margin-left: 1px;
    height: 1em;
    width: auto;
}
#void-recipe .creation .mission-details .stat-powers .power .arrow:before {
    content: "";
    width: 1em;
    height: 1em;
    display: block;
}
#void-recipe .creation .mission-details .stat-powers .power .arrow i {
    position: absolute;
    bottom: -4px;
    right: 0;
    font-size: 20px;
    line-height: 1;
}
#void-recipe .creation .mission-details .stat-powers .power.max,
#void-recipe .creation .mission-details .stat-powers .power.min {
    outline: 1px solid transparent;
    margin-bottom: 4px;
}
#void-recipe .creation .mission-details .stat-powers .power.max {
    outline-color: rgba(255, 255, 255, 0.2);
}
#void-recipe .creation .mission-details .stat-powers .power.min {
    border-style: dotted;
    outline-color: rgba(0, 0, 0, 0.2);
}
#void-recipe .creation .mission-details .stat-powers .power.attack {
    outline-color: #a96667;
}
#void-recipe .creation .mission-details .stat-powers .power.defense {
    outline-color: #687da6;
}
#void-recipe .creation .mission-details .stat-powers .power.speed {
    outline-color: #a591b1;
}

/* -- CREATION >> TARGET LIST -- */

#void-recipe .creation .target-list {
    width: auto;
    top: auto;
    left: 150px;
    right: 150px;
    bottom: 0;
    height: 40px;
    border-radius: 0;
    border-top: 0;
    text-align: center;
    vertical-align: middle;
    z-index: 4;
    /* background-color: magenta; */
}
#void-recipe .creation .target-list > .wrapper {
    display: block;
    position: absolute;
    height: 46px;
    width: auto;
    max-width: 580px;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    white-space: nowrap;
    /* background-color: cyan; */
}
#void-recipe .creation .target-list:hover {
    z-index: 99;
}
#void-recipe .creation .target-list .target {
    display: inline-block;
    float: none;
    position: relative;
    box-sizing: border-box;
    margin: 3px;
    padding: 0;
    width: 60px;
    height: 33px;
    border-radius: 3px;
    text-align: center;
    vertical-align: middle;
    cursor: pointer;
    background-color: transparent;
    filter: opacity(1.0) brightness(1.0);
    transition: background 0.6s, filter 0.1s;
}
#void-recipe .creation .target-list .target > * {
    pointer-events: none;
}
/*
#void-recipe .creation .target-list .target > .hitbox {
    pointer-events: auto;
    display: block;
    position: absolute;
    z-index: 10;
    border: 0 none transparent;
    background-color: transparent;
    width: 30px;
    height: 40px;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
}
*/
#void-recipe .creation .target-list .target > .portal {
    display: block;
    position: absolute;
    z-index: 1;
    pointer-events: auto;
    width: auto;
    height: auto;
    bottom: 26px;
    left: 50%;
    transform: translate(-50%, 0);
    width: 40px;
    height: 40px;
    border: 1px solid transparent;
    border-radius: 50%;
    filter: brightness(0.8) saturate(1.6);
    transition: bottom 0.4s, filter 0.3s;
}
#void-recipe .creation .target-list .target:first-child > .portal {
    width: 50px;
    height: 50px;
}
#void-recipe .creation .target-list .target:hover > .portal {
    bottom: 32px;
    filter: brightness(0.9) saturate(1.8);
}
#void-recipe .creation .target-list .target > .portal:before {
    content: "";
    display: block;
    position: absolute;
    z-index: 1;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background-color: transparent;
    background-image: linear-gradient(0deg, rgba(0, 0, 0, 0.4) 0%, rgba(255, 255, 255, 0.0) 100%);
    mix-blend-mode: soft-light;
    pointer-events: none;
}
#void-recipe .creation .target-list .target > .portal.empty {
    filter: none;
    border-color: #2e2e2e !important;
}
#void-recipe .creation .target-list .target .label {
    display: block;
    position: absolute;
    z-index: 2;
    width: 100%;
    height: 29px;
    bottom: 2px;
    left: 50%;
    transform: translate(-50%, 0);
    padding: 2px;
    font-size: 9px;
    line-height: 13px;
    background-color: transparent;
    border: 0 none transparent;
    border-radius: 3px;
    box-shadow: none;
    transition: background-color 0.3s;
    background-color: rgba(255, 255, 255, 0.0);
}
#void-recipe .creation .target-list .target:hover .label {
    background-color: rgba(255, 255, 255, 0.4);
}
#void-recipe .creation .target-list .target .label .name,
#void-recipe .creation .target-list .target .label .type,
#void-recipe .creation .target-list .target .label .quanta {
    display: block;
    box-sizing: border-box;
    position: absolute;
    z-index: 1;
    margin: 0 auto;
    padding: 0;
    width: auto;
    height: auto;
    vertical-align: top;
    text-align: center;
    font-size: inherit;
    line-height: inherit;
    font-weight: normal;
    white-space: nowrap;
    overflow: hidden;
    border: 0 none transparent;
    background-color: transparent;
    border-radius: 2px;
}
#void-recipe .creation .target-list .target .label .name {
    z-index: 10;
    top: 2px;
    left: 2px;
    right: 2px;
    padding: 2px 4px 3px;
    line-height: 1;
    text-overflow: ellipsis;
    border: 1px solid #2c2c3a;
    background-color: #363645;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}
#void-recipe .creation .target-list .target .label .type,
#void-recipe .creation .target-list .target .label .quanta {
    top: 17px;
    padding: 3px 4px 2px;
    color: #d1d1d1;
    font-size: 8px;
    line-height: 8px;
    border: 1px solid #2c2c3a;
    border-color: #2c2c3a !important;
    border-top-style: none;
    background-color: #363645;
    border-top-left-radius: 0;
    border-top-right-radius: 0;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3);
}
#void-recipe .creation .target-list .target .label .type {
    z-index: 9;
    left: 2px;
    width: 22px;
    right: auto;
    border-bottom-right-radius: 0;
}
#void-recipe .creation .target-list .target .label .quanta {
    z-index: 8;
    right: 2px;
    left: 24px;
    width: auto;
    border-bottom-left-radius: 0;
}
#void-recipe .creation .target-list .target .label .quanta i,
#void-recipe .creation .target-list .target .label .quanta strong {
    display: inline-block;
    margin: 0 auto;
}
#void-recipe .creation .target-list .target .label .quanta i {
    margin-right: 3px;
}
#void-recipe .creation .target-list .target .label .quanta strong {
    font-weight: normal;
}
#void-recipe .creation .target-list .target .label .quanta sup,
#void-recipe .creation .target-list .target .label .quanta sub {
    opacity: 0.5;
}
#void-recipe .creation .target-list .target .image {
    display: block;
    position: absolute;
    z-index: 3;
    width: 40px;
    height: 40px;
    bottom: 30px;
    left: 50%;
    transform-origin: bottom center;
    transform: translate(-50%, 0) scale(1.0);
    /* background-color: rgba(100, 0, 255, 0.1); */
}
#void-recipe .creation .target-list .target .image .sprite {
    display: block;
    pointer-events: none;
    position: relative;
    margin: 0;
    top: 0;
    left: 0;
    transform: translateY(0px);
    animation: void-target-sprite-bounce 0.6s steps(1) infinite;
    /* background-color: rgba(0, 200, 100, 0.1);  */
}
@keyframes void-target-sprite-bounce {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-1px); }
    100% { transform: translateY(0px); }
}
#void-recipe .creation .target-list .target .image .sprite_40x40 {
    top: 0;
    left: 0;
}
#void-recipe .creation .target-list .target .image .sprite_80x80 {
    top: -40px;
    left: -20px;
}
#void-recipe .creation .target-list .target .image .sprite_160x160 {
    top: -80px;
    left: -40px;
}
#void-recipe .creation .target-list .target:hover .image .sprite_40x40 {
    background-position: -40px 0;
}
#void-recipe .creation .target-list .target:hover .image .sprite_80x80 {
    background-position: -80px 0;
}
#void-recipe .creation .target-list .target:hover .image .sprite_160x160 {
    background-position: -160px 0;
}

#void-recipe .creation .target-list .target:hover {
    z-index: 10 !important;
}
#void-recipe .creation .target-list .target:hover .image {
    filter: brightness(1.0);
}
#void-recipe .creation .target-list:hover .target:not(:hover) {
    filter: brightness(0.6);
}

#void-recipe .creation .target-list .target:first-child {
    position: absolute;
    bottom: 4px;
    left: 50%;
    margin: 0;
    transform: translateX(-50%);
}
#void-recipe .creation .target-list .target:first-child .image {
    transform: translate(-50%, 0) scale(2.0);
}
#void-recipe .creation .target-list .target:not(:first-child) {
    bottom: 18px;
}
#void-recipe .creation .target-list .target:not(:first-child):not(:hover) {
    filter: brightness(0.6) saturate(1.4);
}


/* -- VOID PALETTE -- */

#void-recipe .palette {
    margin-top: 6px;
    padding: 0 3px;
    z-index: 10;
}


/* -- VOID SELECTION -- */

#void-recipe .selection {
    background-color: transparent;
    margin: 0 auto;
    padding: 0;
    position: absolute;
    z-index: 30;
    top: 169px;
    left: 12px;
    right: 12px;
    width: auto;
    height: auto;
    background-color: transparent;
    border: 0 none transparent;
    box-shadow: none;
    border-radius: 0 0 3px 3px;
    /* background-color: lime; */
}
#void-recipe .selection .item-list {
    width: auto;
    height: auto;
    min-height: 0;
    background-color: transparent;
    border: 0 none transparent;
    box-shadow: none;
    padding: 0;
    margin: 0;
    /* background-color: cyan; */
}
#void-recipe .selection .item-list .wrapper {
    position: relative;
    top: auto;
    left: auto;
    right: auto;
    bottom: auto;
    height: auto;
    padding: 6px;
    padding-right: 26px;
    /* background-color: magenta; */
}
#void-recipe .selection .item-list .item {
    width: 60px;
    width: calc((100% / 10) - 4px);
    transform: translate(0, 0);
    margin: 0 4px 0 0;
}
#void-recipe .selection .item-list .item:last-child {
    margin-right: 0;
}
/*
#void-recipe .selection .item-list .item .icon {
    transform: translate(-4px, 0) scale(2.0);
}
#void-recipe .selection .item-list .item .icon img {
    filter: saturate(1.2) drop-shadow(0px 0px 1px rgba(255, 255, 255, 0.1));
}
#void-recipe .selection .item-list .item .quantity {
    transform: translate(4px, 0);
}
*/

#void-recipe .selection .item-list .item.recent {
    transform: scale(1.0);
    animation: void-recipe-item-recent 0.5s;
}
@keyframes void-recipe-item-recent {
    0% { transform: scale(1.0); }
    50% { transform: scale(1.4); }
    100% { transform: scale(1.0); }
}
#void-recipe .selection .item-list .item.placeholder {
    filter: opacity(1.0) brightness(1.0);
}

#void-recipe .selection .button {
    display: block;
    position: absolute;
    z-index: 30;
    right: 6px;
    width: 18px;
    height: 18px;
    font-size: 9px;
    line-height: 1;
    text-align: center;
    vertical-align: middle;
    color: #dedede;
    cursor: pointer;
    transform: scale(1.0);
    transition: transform 0.2s, color 0.2s;
    background-color: #242131;
    border: 0 none transparent;
    border-radius: 50%;
}
#void-recipe .selection .button:hover {
    transform: scale(1.2);
    color: #efefef;
}
#void-recipe .selection .button > i {
    display: block;
    margin: 0;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
#void-recipe .selection .button:not(.visible) {
    pointer-events: none;
    filter: opacity(0.4) brightness(0.4);
    cursor: not-allowed;
}
#void-recipe .selection .reset {
    top: 3px;
}
#void-recipe .selection .code {
    bottom: 3px;
}


/* -- ITEM LISTS -- */

#void-recipe .item-list {
    display: block;
    margin: 0 auto;
    width: auto;
    height: auto;
    min-width: 180px;
    min-height: 50px;
    position: relative;
    overflow: visible;
    background-color: #2a2a2a;
    border: 1px solid #1a1a1a;
    border-radius: 6px;
}
#void-recipe .palette .item-list {
    width: auto;
    min-width: 380px;
    height: 300px;
    background-color: transparent;
    border: 0 none transparent;
    box-shadow: none;
}
#void-recipe .item-list .wrapper {
    display: block;
    position: absolute;
    box-sizing: border-box;
    overflow: visible;
    top: 0;
    bottom: 0;
    right: 0;
    left: 0;
    height: 0;
    width: auto;
    padding: 6px;
}

/* -- PALETTE || ITEMS -- */

#void-recipe .palette .item-list .wrapper.float-left {
    right: auto;
    /* background-color: magenta; */
}
#void-recipe .palette .item-list .wrapper.float-right {
    left: auto;
    /* background-color: cyan; */
}

#void-recipe .palette .item-list .wrapper[data-step] {
    z-index: 10;
    top: 18px; /* top padding + margin */
    left: 0;
    bottom: 0;
    right: auto;
    width: auto;
    height: auto;
    min-height: 52px;
    /* min-width: 360px;  */
    min-width: 320px;
    padding: 12px 18px 6px;
    white-space: nowrap;
    line-height: 1;
    border-radius: 3px;
    border: 1px solid #1b1825;
    background-color: #353144;
    box-shadow: 2px 0px 4px rgba(0, 0, 0, 0.3);
    transition: background-color 0.3s, box-shadow 0.2s;
    cursor: pointer;
}
#void-recipe .palette .item-list .wrapper[data-step]:before {
    content: "";
    display: block;
    width: auto;
    height: auto;
    position: absolute;
    top: 6px;
    left: 6px;
    right: 6px;
    bottom: 6px;
    border-radius: 3px;
    box-shadow: inset 0 0 3px rgb(0, 0, 0, 0.6);
    pointer-events: none;
    z-index: 1;
    background-image: url(../images/assets/index-background.gif);
    background-position: center center;
    background-repeat: repeat;
    mix-blend-mode: luminosity;
    opacity: 0.6;
}
#void-recipe .palette .item-list .wrapper[data-step] > .label {
    content: "";
    display: block;
    box-sizing: border-box;
    position: absolute;
    left: auto;
    right: auto;
    width: auto;
    min-width: 130px;
    top: -22px;
    font-size: 11px;
    line-height: 15px;
    height: 24px;
    padding: 3px 6px;
    border: inherit;
    background-color: inherit;
    border-radius: 6px 6px 0 0;
    border-bottom: 1px solid transparent;
    color: #efefef;
    z-index: 2;
    cursor: pointer;
}
#void-recipe .palette .item-list .wrapper[data-step] > .label:before,
#void-recipe .palette .item-list .wrapper[data-step] > .label:after {
    content: "~";
    padding: 0 3px;
    color: #777194;
}
#void-recipe .palette .item-list .wrapper[data-step="1"] > .label:before,
#void-recipe .palette .item-list .wrapper[data-step="1"] > .label:after {
    font-family: 'Font Awesome 5 Pro';
    font-weight: 900;
    -moz-osx-font-smoothing: grayscale;
    -webkit-font-smoothing: antialiased;
    display: inline-block;
    font-style: normal;
    font-variant: normal;
    text-rendering: auto;
    line-height: 1;
    content: "\f005";
}
#void-recipe .palette .item-list .wrapper[data-step] > .label > strong {
    font-weight: normal;
}
#void-recipe .palette .item-list .wrapper[data-step] > .groups {
    display: block;
    box-sizing: border-box;
    width: auto;
    height: auto;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 3;
}
#void-recipe .palette .item-list .wrapper[data-step] .group {
    display: inline-block;
}
#void-recipe .palette .item-list .wrapper[data-step].active {
    box-shadow: 4px 0px 6px rgba(0, 0, 0, 0.4);
}
#void-recipe .palette .item-list .wrapper[data-step="3"].active,
#void-recipe .palette .item-list .wrapper[data-step="4"].active {
    box-shadow: -4px 0px 6px rgba(0, 0, 0, 0.4);
}
#void-recipe .palette .item-list .wrapper[data-step]:not(.active) {
    box-shadow: 2px 0px 4px rgba(0, 0, 0, 0.2);
}
#void-recipe .palette .item-list .wrapper[data-step]:not(.active):hover {
    background-color: #2d293d;
}
#void-recipe .palette .item-list .wrapper[data-step] .group {
    filter: brightness(1.0) saturate(1);
    transition: filter 0.3s;
}
#void-recipe .palette .item-list .wrapper[data-step]:not(.active) .group {
    filter: brightness(0.6) saturate(1.2);
}
@keyframes void-recipe-step-active {
    0% { transform: translate(0, 0) scale(1.0); }
    1% { transform: translate(0, -6px) scale(1.02); }
    100% { transform: translate(0, 0) scale(1.0); }
}
#void-recipe .palette .item-list .wrapper[data-step].active {
    background-color: #2f2b40;
    border-color: #4a4360;
    border-color: rgba(255, 255, 255, 0.1);
    animation: void-recipe-step-active 0.4s 1;
}
#void-recipe .palette .item-list .wrapper[data-step]:not(.active) {
    background-color: #242032;
}


#void-recipe .palette .item-list .wrapper[data-step="1"].active { background-color: #2f2b40; }
#void-recipe .palette .item-list .wrapper[data-step="1"]:not(.active) { background-color: #242032; }

#void-recipe .palette .item-list .wrapper[data-step="2"].active { background-color: #2f2b40; }
#void-recipe .palette .item-list .wrapper[data-step="2"]:not(.active) { background-color: #2a2032; }

#void-recipe .palette .item-list .wrapper[data-step="3"].active { background-color: #2f2b40; }
#void-recipe .palette .item-list .wrapper[data-step="3"]:not(.active) { background-color: #302032; }

#void-recipe .palette .item-list .wrapper[data-step="4"].active { background-color: #2f2b40; }
#void-recipe .palette .item-list .wrapper[data-step="4"]:not(.active) { background-color: #32202e; }

#void-recipe .palette .item-list .wrapper[data-step="5"].active { background-color: #2f2b40; }
#void-recipe .palette .item-list .wrapper[data-step="5"]:not(.active) { background-color: #322028; }

#void-recipe .palette .item-list .wrapper[data-step="1"] { left: 0; }
#void-recipe .palette .item-list .wrapper[data-step="2"] { left: calc((100% - 320px) * 0.25);  }
#void-recipe .palette .item-list .wrapper[data-step="3"] { left: calc((100% - 320px) * 0.50);  }
#void-recipe .palette .item-list .wrapper[data-step="4"] { left: calc((100% - 320px) * 0.75);  }
#void-recipe .palette .item-list .wrapper[data-step="5"] { left: calc((100% - 320px) * 1.00); }

#void-recipe .palette .item-list .wrapper[data-layer="0"] { z-index: 0;  }

#void-recipe .palette .item-list .wrapper[data-layer="1"] { z-index: 20;  }
#void-recipe .palette .item-list .wrapper[data-layer="2"] { z-index: 19;  }
#void-recipe .palette .item-list .wrapper[data-layer="3"] { z-index: 18;  }
#void-recipe .palette .item-list .wrapper[data-layer="4"] { z-index: 17;  }
#void-recipe .palette .item-list .wrapper[data-layer="5"] { z-index: 16;  }

#void-recipe .palette .item-list .wrapper[data-side="left"]:first-child { box-shadow: 4px 0px 6px rgba(0, 0, 0, 0.2); }
#void-recipe .palette .item-list .wrapper[data-side="left"] { box-shadow: 2px 0px 4px rgba(0, 0, 0, 0.2); }
#void-recipe .palette .item-list .wrapper[data-side="middle"] { box-shadow: 0 0px 4px rgba(0, 0, 0, 0.2); }
#void-recipe .palette .item-list .wrapper[data-side="right"] { box-shadow: -2px 0px 4px rgba(0, 0, 0, 0.2); }
#void-recipe .palette .item-list .wrapper[data-side="right"]:last-child { box-shadow: -4px 0px 6px rgba(0, 0, 0, 0.2); }

#void-recipe .palette .item-list .wrapper[data-layer="1"][data-side="left"]:first-child { box-shadow: 4px 0px 6px rgba(0, 0, 0, 0.4); }
#void-recipe .palette .item-list .wrapper[data-layer="1"][data-side="left"] { box-shadow: 2px 0px 4px rgba(0, 0, 0, 0.4); }
#void-recipe .palette .item-list .wrapper[data-layer="1"][data-side="middle"] { box-shadow: 0 0px 4px rgba(0, 0, 0, 0.4); }
#void-recipe .palette .item-list .wrapper[data-layer="1"][data-side="right"] { box-shadow: -2px 0px 4px rgba(0, 0, 0, 0.4); }
#void-recipe .palette .item-list .wrapper[data-layer="1"][data-side="right"]:last-child { box-shadow: -4px 0px 6px rgba(0, 0, 0, 0.4); }

#void-recipe .palette .item-list .wrapper[data-side="left"]:first-child > .label { left: 6px; }
#void-recipe .palette .item-list .wrapper[data-side="left"] > .label { left: 50px; }
#void-recipe .palette .item-list .wrapper[data-side="middle"] > .label { left: 50%; transform: translateX(-50%); }
#void-recipe .palette .item-list .wrapper[data-side="right"] > .label { right: 50px; }
#void-recipe .palette .item-list .wrapper[data-side="right"]:last-child > .label { right: 6px; }

#void-recipe .palette .item-list .wrapper[data-step].disabled {
    cursor: not-allowed;
}

#void-recipe .palette .item-list .wrapper[data-step].locked {
    pointer-events: none;
    filter: saturate(0.6) brightness(0.8);
    cursor: not-allowed;
}
#void-recipe .palette .item-list .wrapper[data-step].locked .group {
    filter: brightness(0.0);
}
#void-recipe .palette .item-list .wrapper[data-step].locked > .label strong,
#void-recipe .palette .item-list .wrapper[data-step].locked > .label:after {
    display: none;
}
#void-recipe .palette .item-list .wrapper[data-step].locked > .label:before {
    content: "~ ??? ~";
}

/* -- ITEM LIST || ITEMS -- */

#void-recipe .item-list .item {
    display: block;
    box-sizing: border-box;
    user-select: none;
    float: left;
    width: 54px;
    height: 34px;
    margin: 0 2px 2px 0;
    border: 1px solid #1A1A1A;
    background-color: #262626;
    border-radius: 3px;
    position: relative;
    cursor: pointer;
    filter: opacity(1.0) brightness(1.0);
    box-shadow: 0 0 2px rgba(0, 0, 0, 0);
    transition: filter 0.3s, background-color 0.3s, box-shadow 0.3s, transform 0.3s;
}
#void-recipe .item-list .item:hover {
    background-color: #333333;
    box-shadow: 0 0 2px rgba(0, 0, 0, 0.6);
    z-index: 99 !important;
}
#void-recipe .item-list .item:before {
    content: "";
    display: block;
    position: absolute;
    z-index: 2;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 3px;
    background-color: #262626;
    transition: top 0.1s, right 0.1s, bottom 0.1s, left 0.1s, background-color 0.1s, opacity 0.1s;
    background-image: linear-gradient(180deg, #2f2b41, #331f28);
    background-blend-mode: normal;
    opacity: 1.0;
}
#void-recipe .item-list .item:hover:before {
    opacity: 0.0;
}
#void-recipe .item-list .item:after {
    content: "";
    display: block;
    position: absolute;
    z-index: 2;
    top: 4px;
    left: 4px;
    right: 4px;
    bottom: 4px;
    background-color: #363636;
    border-radius: 3px;
    transition: top 0.1s, right 0.1s, bottom 0.1s, left 0.1s, background-color 0.1s;
}
#void-recipe .item-list .item:hover:after {
    top: 2px;
    bottom: 6px;
    background-color: #434343;
}
#void-recipe .item-list .item.active {
    outline: 2px solid rgba(255, 255, 255, 0.6);
}
#void-recipe .item-list .item.active:after {
    background-color: #4f4f4f;
}
#void-recipe .item-list .item.active .icon {
    filter: brightness(1.4);
}
#void-recipe .item-list .item[data-quantity="0"] {
    filter: opacity(0.6) brightness(0.9);
    pointer-events: none;
    cursor: not-allowed;
}
#void-recipe .item-list .item[data-quantity="0"][data-base-quantity="0"] .icon {
    filter: brightness(0);
    opacity: 0.6;
}
#void-recipe .item-list[data-select="active"] .item:not(.active) {
    filter: opacity(0.6) brightness(0.9);
    pointer-events: none;
    cursor: not-allowed;
}
#void-recipe .item-list[data-select="active"] .item:not(.active) .icon {
    filter: brightness(0.4);
    opacity: 0.6;
}
#void-recipe .item-list .item.disabled {
    filter: brightness(0.9);
    pointer-events: none;
    cursor: not-allowed;
}
#void-recipe .item-list .item.disabled .icon {
    filter: brightness(0.4);
}

#void-recipe .item-list .item.placeholder {
    filter: opacity(0.6) brightness(0.9);
    pointer-events: none;
    cursor: not-allowed;
    border-color: #1b1825;
    background-color: #1b1825;
    background-color: #1b1825cc;
    box-shadow: inset 2px 2px 4px rgba(0, 0, 0, 0.1);
}
#void-recipe .item-list .item.placeholder:before {
    background-color: #242033;
    display: none;
}
#void-recipe .item-list .item.placeholder:after {
    display: none;
}
#void-recipe .item-list .item.placeholder .icon {
    filter: brightness(0);
    opacity: 0.6;
}

#void-recipe .item-list .item .name {
    display: block;
    position: absolute;
    pointer-events: none;
    z-index: 1;
    bottom: 0;
    left: 0;
    right: 0;
    text-align: center;
    font-size: 9px;
    line-height: 12px;
    height: auto;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    padding: 6px 6px 36px;
    background-color: #262626;
    background-image: linear-gradient(180deg, #2f2b4100, #331f2800);
    border: 1px solid #1A1A1A;
    box-shadow: 0 0 6px rgba(0, 0, 0, 0);
    border-radius: 6px;
    pointer-events: none;
    opacity: 0;
    height: 0;
    transition: height 0.4s, bottom 0.2s, left 0.2s, right 0.2s, opacity 0.2s, background-color 0.2s, box-shadow 0.2s;
}
#void-recipe .item-list .item .name.one-line {
    line-height: 26px;
}
#void-recipe .item-list .item:hover .name {
    bottom: -3px;
    left: -3px;
    right: -3px;
    opacity: 1;
    height: 26px;
    padding-bottom: 36px;
    background-color: #333333;
    background-image: linear-gradient(180deg, #2f2b41, #331f28);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

#void-recipe .item-list .item .icon {
    display: block;
    position: absolute;
    z-index: 3;
    top: -3px;
    left: -4px;
    pointer-events: none;
    transform: translate(0, 0) scale(1.0);
    transition: transform 0.2s;
}
#void-recipe .item-list .item .icon img {
    display: block;
    margin: 0;
}
#void-recipe .item-list .item:hover .icon {
    transform: translate(0, -2px);
}

#void-recipe .item-list .item .quantity {
    display: block;
    position: absolute;
    z-index: 4;
    top: 10px;
    right: 8px;
    text-align: right;
    padding: 2px;
    font-size: 9px;
    line-height: 11px;
    color: #ffffff;
    transform: translate(0, 0);
    transition: transform 0.2s;
}
#void-recipe .item-list .item .quantity:before {
    content: "\0000d7";
}
#void-recipe .item-list .item:hover .quantity {
    transform: translate(0, -2px);
}

/* -- ITEM LIST || GROUPS -- */

#void-recipe .item-list .group {
    display: block;
    text-align: center;
    vertical-align: middle;
    box-sizing: content-box;
    width: auto;
    padding: 4px 2px 2px 4px;
    margin: 6px auto 0;
    overflow: visible;
    background-color: rgba(77, 77, 77, 0.2);
    border-radius: 3px;
    position: relative;
    cursor: default;
    z-index: 10;
}
#void-recipe .item-list .group:empty {
    display: none;
}
#void-recipe .item-list .group[data-rowline="1"] {
    margin-top: 0;
}
#void-recipe .item-list .wrapper.float-left .group {
    float: left;
    margin-left: 0;
    margin-right: 6px;
}
#void-recipe .item-list .wrapper.float-right .group {
    float: right;
    margin-right: 0;
    margin-left: 6px;
}
#void-recipe .item-list .wrapper.float-other .group {
    float: none;
    margin: 0 6px;
    display: none;
}
#void-recipe .item-list .group .item {
    transition: all 0.3s;
}
#void-recipe .item-list .group:hover .item:not(:hover) {
    border-color: transparent;
    background-color: #2e2e2e;
}
#void-recipe .item-list .group:hover .item:not(:hover) .quantity {
    color: #cacaca;
}


#void-recipe .item-list .float-left .group.clear { clear: left; }
#void-recipe .item-list .float-right .group.clear { clear: right; }
#void-recipe .item-list .wrapper .group + .clear { display: block; width: auto; clear: both; }

#void-recipe .item-list .group[data-colspan="1"] { width: calc((54px + 2px) * 1); }
#void-recipe .item-list .group[data-colspan="2"] { width: calc((54px + 2px) * 2); }
#void-recipe .item-list .group[data-colspan="3"] { width: calc((54px + 2px) * 3); }
#void-recipe .item-list .group[data-colspan="4"] { width: calc((54px + 2px) * 4); }
#void-recipe .item-list .group[data-colspan="5"] { width: calc((54px + 2px) * 5); }
#void-recipe .item-list .group[data-colspan="6"] { width: calc((54px + 2px) * 6); }
#void-recipe .item-list .group[data-colspan="7"] { width: calc((54px + 2px) * 7); }
#void-recipe .item-list .group[data-colspan="8"] { width: calc((54px + 2px) * 8); }
#void-recipe .item-list .group[data-colspan="9"] { width: calc((54px + 2px) * 9); }
#void-recipe .item-list .group[data-colspan="10"] { width: calc((54px + 2px) * 10); }

