
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
    width: 700px;
    height: auto;
    background-color: #262626;
    border: 1px solid #1A1A1A;
    border-radius: 3px;
    padding: 8px;
    margin: 0 auto;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.25);
}

/* -- BASIC STRUCTURES -- */

#void-recipe .title,
#void-recipe .creation,
#void-recipe .selection,
#void-recipe .palette {
    display: block;
    width: auto;
    height: auto;
    text-align: center;
    margin: 4px auto;
    position: relative;
}
#void-recipe .title {
    margin-top: 0;
}
#void-recipe .creation {
    z-index: 1;
}
#void-recipe .selection {
    z-index: 2;
}
#void-recipe .palette {
    z-index: 3;
}

#void-recipe .title:after,
#void-recipe .palette:after,
#void-recipe .void:after,
#void-recipe .mission:after,
#void-recipe .palette .item-list:after,
#void-recipe .palette .item-list .wrapper:after,
#void-recipe .palette .item-list .wrapper .group:after,
#void-recipe .selection .item-list:after,
#void-recipe .selection .item-list .wrapper:after,
#void-recipe .creation .target-list:after,
#void-recipe .creation .mission-details:after {
    content: "";
    display: block;
    clear: both;
}

#void-recipe .title {

}
#void-recipe .title strong {
    display: block;
    width: auto;
    margin: 0 auto;
    padding: 6px 12px;
    border: 1px solid #1A1A1A;
    background-color: #1e1e1e;
    border-radius: 3px;
    color: #fefefe;
    font-size: 12px;
    line-height: 16px;
    text-align: center;
}

#void-recipe .creation:before,
#void-recipe .selection:before,
#void-recipe .palette:before {
    content: "...";
    display: block;
    position: absolute;
    z-index: 10;
    left: 0;
    right: 0;
    top: 0;
    font-size: 9px;
    line-height: 12px;
    color: #bababa;
    border-radius: 3px 3px 0 0;
    border: 0 none transparent;
    background-color: #1a1a1a;
    background-color: rgba(30, 30, 30, 0.8);
    padding: 2px 6px;
}
#void-recipe .creation:before {
    content: "Mission Preview";
}
#void-recipe .selection:before {
    content: "Selected Items";
}
#void-recipe .palette:before {
    content: "Your Inventory";
    display: none;
}
#void-recipe .creation .loading,
#void-recipe .palette .loading,
#void-recipe .selection .loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.6;
}
#void-recipe .creation .mission-details,
#void-recipe .selection .item-list,
#void-recipe .palette .item-list {
    padding-top: 16px;
}
#void-recipe .selection .item-list .wrapper,
#void-recipe .palette .item-list .wrapper {
    top: 16px;
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
#void-recipe .selection .item-list {
    width: 680px;
    height: 48px;
    box-shadow: inset 2px 14px 10px rgba(0, 0, 0, 0.1);
}
#void-recipe .palette .item-list {
    width: 680px;
    height: 282px;
    /* box-shadow: inset -2px -4px 12px rgba(0, 0, 0, 0.1); */
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
    /* min-width: 72px; */
    min-width: 40%;
    padding: 12px 18px 6px;
    white-space: nowrap;
    line-height: 1;
    border-radius: 3px;
    border: 1px solid #1b1825;
    background-color: #353144;
    box-shadow: 2px 0px 4px rgba(0, 0, 0, 0.3);
    transition: background-color 0.3s, box-shadow 0.2s;
}
#void-recipe .palette .item-list .wrapper[data-step] .group {
    display: inline-block;
}
#void-recipe .palette .item-list .wrapper[data-step].active {
    box-shadow: 4px 0px 6px rgba(0, 0, 0, 0.4);
}
#void-recipe .palette .item-list .wrapper[data-step]:not(.active) {
    box-shadow: 2px 0px 4px rgba(0, 0, 0, 0.2);
}
#void-recipe .palette .item-list .wrapper[data-step] .group {
    filter: brightness(1.0) saturate(1);
    transition: filter 0.3s;
}
#void-recipe .palette .item-list .wrapper[data-step]:not(.active) .group {
    filter: brightness(0.6) saturate(1.2);
}

#void-recipe .palette .item-list .wrapper[data-step="1"] {
    left: 0;
    right: auto;
}
#void-recipe .palette .item-list .wrapper[data-step="2"] {
    left: 20%;
    right: auto;
}
#void-recipe .palette .item-list .wrapper[data-step="3"] {
    left: 40%;
    right: auto;
}
#void-recipe .palette .item-list .wrapper[data-step="4"] {
    left: 60%;
    right: 0;
}

#void-recipe .palette .item-list[data-step="1"] .wrapper[data-step="1"] { z-index: 14; }
#void-recipe .palette .item-list[data-step="1"] .wrapper[data-step="2"] { z-index: 13; }
#void-recipe .palette .item-list[data-step="1"] .wrapper[data-step="3"] { z-index: 12; }
#void-recipe .palette .item-list[data-step="1"] .wrapper[data-step="4"] { z-index: 11; }

#void-recipe .palette .item-list[data-step="2"] .wrapper[data-step="1"] { z-index: 13; }
#void-recipe .palette .item-list[data-step="2"] .wrapper[data-step="2"] { z-index: 14; }
#void-recipe .palette .item-list[data-step="2"] .wrapper[data-step="3"] { z-index: 12; }
#void-recipe .palette .item-list[data-step="2"] .wrapper[data-step="4"] { z-index: 11; }

#void-recipe .palette .item-list[data-step="3"] .wrapper[data-step="1"] { z-index: 12; }
#void-recipe .palette .item-list[data-step="3"] .wrapper[data-step="2"] { z-index: 13; }
#void-recipe .palette .item-list[data-step="3"] .wrapper[data-step="3"] { z-index: 14; }
#void-recipe .palette .item-list[data-step="3"] .wrapper[data-step="4"] { z-index: 11; }

#void-recipe .palette .item-list[data-step="4"] .wrapper[data-step="1"] { z-index: 11; }
#void-recipe .palette .item-list[data-step="4"] .wrapper[data-step="2"] { z-index: 12; }
#void-recipe .palette .item-list[data-step="4"] .wrapper[data-step="3"] { z-index: 13; }
#void-recipe .palette .item-list[data-step="4"] .wrapper[data-step="4"] { z-index: 14; }

#void-recipe .palette .item-list .wrapper[data-step] > .label {
    content: "";
    display: block;
    position: absolute;
    left: 6px;
    width: 100px;
    top: -16px;
    height: 12px;
    line-height: 12px;
    font-size: 9px;
    padding: 2px 6px;
    border: inherit;
    background-color: inherit;
    border-radius: 6px 6px 0 0;
    border-bottom: 0 none transparent;
    color: #efefef;
    z-index: 5;
    cursor: pointer;
}
#void-recipe .palette .item-list .wrapper[data-step] > .label:before,
#void-recipe .palette .item-list .wrapper[data-step] > .label:after {
    content: "~";
    padding: 0 3px;
    color: #777194;
}

/* -- ITEM LIST || ITEMS -- */

#void-recipe .item-list .item {
    display: block;
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
#void-recipe .selection .item-list .item {
    width: 62px;
    width: calc((100% / 10) - 6px);
    margin: 0 4px 4px 0;
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
    top: 4px;
    left: 4px;
    right: 4px;
    bottom: 4px;
    background-color: #363636;
    border-radius: 3px;
    transition: top 0.1s, right 0.1s, bottom 0.1s, left 0.1s, background-color 0.1s;
}
#void-recipe .item-list .item:hover:before {
    top: 2px;
    bottom: 6px;
    background-color: #434343;
}
#void-recipe .item-list .item.active {
    filter: brightness(1.5);
    outline: 2px solid rgba(255, 255, 255, 0.6);
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

#void-recipe .item-list .item.placeholder {
    filter: opacity(0.6) brightness(0.9);
    pointer-events: none;
    cursor: not-allowed;
    border-color: transparent;
    background-color: #202020;
    box-shadow: inset 1px 1px 2px rgba(0, 0, 0, 0.6);
}
#void-recipe .item-list .item.placeholder:before {
    background-color: #202020;
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
    width: auto;
    padding: 4px 2px 2px 4px;
    margin: 6px auto 0;
    overflow: visible;
    background-color: rgba(77, 77, 77, 0.2);
    border-radius: 3px;
    position: relative;
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

#void-recipe .item-list .group[data-colspan="1"] { width: calc(((54px) + 4px) * 1); }
#void-recipe .item-list .group[data-colspan="2"] { width: calc(((54px) + 4px) * 2); }
#void-recipe .item-list .group[data-colspan="3"] { width: calc(((54px) + 4px) * 3); }
#void-recipe .item-list .group[data-colspan="4"] { width: calc(((54px) + 4px) * 4); }
#void-recipe .item-list .group[data-colspan="5"] { width: calc(((54px) + 4px) * 5); }
#void-recipe .item-list .group[data-colspan="6"] { width: calc(((54px) + 4px) * 6); }
#void-recipe .item-list .group[data-colspan="7"] { width: calc(((54px) + 4px) * 7); }
#void-recipe .item-list .group[data-colspan="8"] { width: calc(((54px) + 4px) * 8); }
#void-recipe .item-list .group[data-colspan="9"] { width: calc(((54px) + 4px) * 9); }
#void-recipe .item-list .group[data-colspan="10"] { width: calc(((54px) + 4px) * 10); }


/* -- SELECTION || ITEM LIST & BUTTONS -- */

#void-recipe .selection {

}
#void-recipe .selection .item-list {

}
#void-recipe .selection .item-list .item {
    transform: translate(0, 0);
}
#void-recipe .selection .item-list .item .icon {
    transform: translate(0, 0) scale(2.0);
}

#void-recipe .selection .item-list .item.recent {
    transform: scale(1.0);
    animation: void-recipe-item-recent 0.5s;
}
@keyframes void-recipe-item-recent {
    0% { transform: scale(1.0); }
    50% { transform: scale(1.4); }
    100% { transform: scale(1.0); }
}

#void-recipe .selection .reset {
    display: block;
    position: absolute;
    z-index: 30;
    top: 0;
    right: 0;
    width: 24px;
    height: 24px;
    font-size: 18px;
    line-height: 24px;
    text-align: center;
    vertical-align: middle;
    color: #a1a1a1;
    cursor: pointer;
    transform: scale(1.0);
    transition: transform 0.2s, color 0.2s;
}
#void-recipe .selection .reset:hover {
    transform: scale(1.2);
    color: #efefef;
}
#void-recipe .selection .reset > i {
    display: block;
    margin: 0;
}
#void-recipe .selection .reset:not(.visible) {
    pointer-events: none;
    display: none;
}

/* -- CREATION || TARGET LIST & MISSION DETAILS -- */

#void-recipe .creation {
    height: 140px;
    border: 1px solid #1a1a1a;
    border-radius: 6px;
    box-shadow: 0 0 6px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}
#void-recipe .creation .mission-details,
#void-recipe .creation .target-list,
#void-recipe .creation .battle-field {
    display: block;
    margin: 0 auto;
    width: auto;
    height: auto;
    min-width: 180px;
    min-height: 50px;
    overflow: visible;
    border: 1px solid #1A1A1A;
    border-radius: 6px;
    position: absolute;
    left: 0;
    right: 0;
    z-index: 1;
}

#void-recipe .creation .battle-field {
    overflow: hidden;
    border: 0 none transparent;
    position: absolute;
    top: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 1;
}
#void-recipe .creation .battle-field .sprite.background,
#void-recipe .creation .battle-field .sprite.foreground {
    position: absolute;
    margin: 0 auto;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
}
#void-recipe .creation .battle-field .sprite.background {
    background-repeat: repeat;
    background-position: center center;
    z-index: 1;
}
#void-recipe .creation .battle-field .sprite.foreground {
    top: auto;
    height: 20%;
    z-index: 2;
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
    background-color: #292929;
}

#void-recipe .creation .mission-details {
    height: auto;
    min-height: 26px;
    border-radius: 6px 6px 0 0;
    border-bottom: 0;
    z-index: 2;
}
#void-recipe .creation .mission-details:hover {
    z-index: 9;
}
#void-recipe .creation .mission-details .powers-list {
    display: block;
    width: auto;
}
#void-recipe .creation .mission-details .powers-list ul {
    display: block;
    box-sizing: border-box;
    width: 100%;
    height: 100%;
    margin: 0 auto;
    padding: 3px;
    font-size: 11px;
    line-height: 13px;
    color: #efefef;
}
#void-recipe .creation .mission-details .powers-list ul li {
    display: block;
    box-sizing: border-box;
    float: left;
    width: calc(100% / 5);
    text-align: center;
    padding: 2px 3px 3px;
}
#void-recipe .creation .mission-details .powers-list ul:after,
#void-recipe .creation .mission-details .powers-list ul li:after {
    content: "";
    display: block;
    clear: both;
}
#void-recipe .creation .mission-details .powers-list ul li:nth-child(odd) {
    background-color: rgba(255, 255, 255, 0.02);
}
#void-recipe .creation .mission-details .powers-list ul li .token,
#void-recipe .creation .mission-details .powers-list ul li .value {
    display: inline-block;
    margin: 0 4px;
}
#void-recipe .creation .mission-details .powers-list ul li .token {
    text-decoration: underline;
}
#void-recipe .creation .mission-details .powers-list ul li .value {
    font-weight: bold;
}
#void-recipe .creation .mission-details .powers-list ul li .value .overflow {
    display: inline-block;
    margin-left: 4px;
    opacity: 0.4;
    font-weight: normal;
}
#void-recipe .creation .mission-details .powers-list ul li .token.type {
    text-decoration: none;
    padding: 0 3px;
    border-radius: 3px;
}

#void-recipe .creation .target-list {
    bottom: 0;
    height: 80px;
    border-radius: 0 0 6px 6px;
    border-top: 0;
    text-align: center;
    vertical-align: middle;
    z-index: 3;
}
#void-recipe .creation .target-list .target {
    display: inline-block;
    position: relative;
    box-sizing: border-box;
    text-align: center;
    vertical-align: middle;
    margin: 6px;
    padding: 0;
    width: 60px;
    height: 60px;
    width: calc((100% / 9) - 12px);
    height: calc(100% - 12px);
    border-radius: 3px;
    text-align: center;
    background-color: transparent;
    cursor: pointer
    filter: opacity(1.0) brightness(1.0);
    outline: 0 none transparent;
    transition: filter 0.1s;
    /* background-color: #2d2c39;  */
    /* background-color: rgba(255, 0, 100, 0.1);  */
}
#void-recipe .creation .target-list .target > * {
    pointer-events: none;
}
#void-recipe .creation .target-list .target:hover {
    filter: brightness(1.1);
    outline: 2px solid rgba(255, 255, 255, 0.6);
}
#void-recipe .creation .target-list .target > .type {
    display: block;
    position: absolute;
    z-index: 1;
    width: auto;
    height: auto;
    bottom: 9px;
    left: 50%;
    transform: translate(-50%, 0);
    width: 50px;
    height: 50px;
    border: 1px solid transparent;
    border-radius: 50%;
    filter: opacity(1.0) brightness(0.7) saturate(0.8);
    pointer-events: none;
}
#void-recipe .creation .target-list .target > .type.empty {
    filter: none;
    border-color: #2e2e2e !important;
}
#void-recipe .creation .target-list .target .image {
    display: block;
    position: absolute;
    z-index: 2;
    width: 40px;
    height: 40px;
    bottom: 14px;
    left: 50%;
    transform: translate(-50%, -50%) scale(2.0);
    /* background-color: rgba(100, 0, 255, 0.1);  */
}
#void-recipe .creation .target-list .target .label {
    display: block;
    position: absolute;
    z-index: 3;
    width: 100%;
    height: auto;
    bottom: 0;
    left: 50%;
    transform: translate(-50%, 0);
    font-size: 9px;
    line-height: 13px;
    background-color: #22222b;
    border: 1px solid #1d1d26;
    border-radius: 2px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    /* background-color: rgba(100, 100, 0, 0.1); */
}
#void-recipe .creation .target-list .target .label .name {
    display: block;
    margin: 0 auto;
    width: 90%;
    width: calc(100% - 8px);
    font-size: inherit;
    line-height: inherit;
    font-weight: normal;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
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

/* -- PANEL & GROUP COLORS -- */

#void-recipe .creation .mission-details {
    background-color: #2d2c3a;
    background-color: rgb(45, 44, 58, 0.9);
}
#void-recipe .creation .battle-field:after {
    background-color: #292834;
    background-color: rgb(41, 40, 52, 0.8);
}
#void-recipe .selection .item-list {
    background-color: #25232e;
}
#void-recipe .palette .item-list {
    background-color: transparent;
    border-color: transparent;
}
#void-recipe .palette .item-list .wrapper[data-step].active {
    background-color: #2f2b40;
}
#void-recipe .palette .item-list .wrapper[data-step]:not(.active) {
    background-color: #242032;
}
#void-recipe .palette .item-list .wrapper[data-step]:not(.active):hover {
    background-color: #2d293d;
}
