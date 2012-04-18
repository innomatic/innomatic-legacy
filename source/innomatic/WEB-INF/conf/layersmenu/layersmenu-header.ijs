<!-- {packageName} {version} {copyright} {author} -->

<script language="JavaScript" type="text/javascript">
<!--

var thresholdY = {thresholdY};
var abscissaStep = {abscissaStep};

listl = new Array();
{listl}
var numl = {numl};

father = new Array();
for (i=1; i<={nodesCount}; i++) {
	father["L" + i] = "";
}
{father}

lwidth = new Array();
var lwidthDetected = 0;

function moveLayers() {
	if (!lwidthDetected) {
		for (i=1; i<=numl; i++) {
			lwidth[listl[i]] = getOffsetWidth(listl[i]);
		}
		lwidthDetected = 1;
	}
{moveLayers}
}

back = new Array();
for (i=1; i<={nodesCount}; i++) {
	back["L" + i] = 0;
}

// -->
</script>

<!-- {packageName} {version} {copyright} {author} -->
