#!/usr/bin/env bash

#Already minified
cp node_modules/jquery/dist/jquery.min.js dev/assets/min.js/
cp node_modules/bootstrap/dist/js/bootstrap.min.js dev/assets/min.js/
cp node_modules/bootstrap-notify/bootstrap-notify.min.js dev/assets/min.js/
cp node_modules/moment/min/moment-with-locales.min.js dev/assets/min.js/
cp node_modules/bootstrap-select/dist/js/bootstrap-select.min.js dev/assets/min.js

#Need to be minified
cp node_modules/chart.js/dist/Chart.js dev/assets/min.js/
cp node_modules/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js dev/assets/min.js/
cp node_modules/jquery.cookie/jquery.cookie.js dev/assets/min.js/

#Source maps
cp node_modules/bootstrap-select/dist/js/bootstrap-select.js.map dev/assets/min.js