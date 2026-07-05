/**
 * Shared chart renderers for Desiderio Content Blocks.
 *
 * Templates pass data via data-* attributes. This file owns all chart
 * bootstrapping so Fluid templates stay CSP-safe and shadcn-token driven.
 */
(function () {
  'use strict';

  var SVG_NS = 'http://www.w3.org/2000/svg';
  var chartColors = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'];

  function each(selector, callback) {
    Array.prototype.forEach.call(document.querySelectorAll(selector), callback);
  }

  function once(root) {
    if (!root || root.dataset.desiderioChartReady === '1') {
      return false;
    }
    root.dataset.desiderioChartReady = '1';
    return true;
  }

  function parseJson(root, attr, fallback) {
    var raw = root.getAttribute(attr);
    if (!raw) {
      return fallback;
    }
    try {
      return JSON.parse(raw);
    } catch (error) {
      return fallback;
    }
  }

  function listValues(input) {
    if (Array.isArray(input)) {
      return input;
    }

    if (input && typeof input === 'object') {
      return Object.values(input);
    }

    return [];
  }

  function numericRows(rows, positiveOnly) {
    rows = listValues(rows);

    return rows.map(function (row) {
      return {
        label: row && row.label ? String(row.label) : '',
        value: row && row.value != null ? Number(row.value) : NaN
      };
    }).filter(function (row) {
      return !Number.isNaN(row.value) && (!positiveOnly || row.value > 0);
    });
  }

  function numericValues(rows) {
    rows = listValues(rows);

    return rows.map(function (row) {
      if (typeof row === 'number') {
        return row;
      }
      return row && row.value != null ? Number(row.value) : NaN;
    }).filter(function (value) {
      return !Number.isNaN(value);
    });
  }

  function clear(node) {
    while (node.firstChild) {
      node.removeChild(node.firstChild);
    }
  }

  function svgElement(name, attrs) {
    var element = document.createElementNS(SVG_NS, name);
    Object.keys(attrs || {}).forEach(function (key) {
      element.setAttribute(key, attrs[key]);
    });
    return element;
  }

  function htmlElement(name, className, text) {
    var element = document.createElement(name);
    if (className) {
      element.className = className;
    }
    if (text != null) {
      element.textContent = text;
    }
    return element;
  }

  function addTitle(element, text) {
    var title = svgElement('title');
    title.textContent = text;
    element.appendChild(title);
  }

  function setAnimated(element, index) {
    element.setAttribute('data-chart-animate', '');
    element.style.setProperty('--chart-delay', (index * 42) + 'ms');
  }

  function animateChart(svg) {
    if (!svg || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
      return;
    }
    window.requestAnimationFrame(function () {
      svg.setAttribute('data-chart-animated', 'true');
    });
  }

  function formatValue(value) {
    if (Math.abs(value) >= 1000) {
      return value.toLocaleString(undefined, { maximumFractionDigits: 1 });
    }
    return String(value);
  }

  function formatAxisValue(value, unit) {
    return formatValue(value) + (unit ? ' ' + unit : '');
  }

  function formatCompactAxisValue(value) {
    var absolute = Math.abs(value);
    if (absolute >= 1000000) {
      return (value / 1000000).toLocaleString(undefined, { maximumFractionDigits: 1 }) + 'M';
    }
    if (absolute >= 1000) {
      return (value / 1000).toLocaleString(undefined, { maximumFractionDigits: absolute >= 10000 ? 0 : 1 }) + 'k';
    }
    return formatValue(value);
  }

  function niceStep(value) {
    var exponent = Math.floor(Math.log10(value || 1));
    var magnitude = Math.pow(10, exponent);
    var normalized = value / magnitude;
    var niceNormalized = normalized <= 1 ? 1 : normalized <= 2 ? 2 : normalized <= 5 ? 5 : 10;

    return niceNormalized * magnitude;
  }

  function yScale(values, tickTarget) {
    var max = Math.max.apply(null, values.map(function (row) { return row.value; }));
    var min = Math.min.apply(null, values.map(function (row) { return row.value; }));
    var range = max - min || Math.abs(max) || 1;
    var step = niceStep(range / Math.max(1, (tickTarget || 5) - 1));
    var domainMin = Math.floor(min / step) * step;
    var domainMax = Math.ceil(max / step) * step;
    var ticks = [];

    if (domainMin === domainMax) {
      domainMin -= step;
      domainMax += step;
    }

    for (var tick = domainMin; tick <= domainMax + step / 2; tick += step) {
      ticks.push(Number(tick.toFixed(6)));
    }

    return {
      min: domainMin,
      max: domainMax,
      ticks: ticks
    };
  }

  function yScaleFromZero(values, tickTarget) {
    var max = Math.max.apply(null, values.map(function (row) { return row.value; })) || 1;
    var step = niceStep(max / Math.max(1, (tickTarget || 5) - 1));
    var domainMax = Math.ceil(max / step) * step || step;
    var ticks = [];

    for (var tick = 0; tick <= domainMax + step / 2; tick += step) {
      ticks.push(Number(tick.toFixed(6)));
    }

    return {
      min: 0,
      max: domainMax,
      ticks: ticks
    };
  }

  function renderLegend(root, values) {
    var legend = root.querySelector('.chart__legend');
    if (!legend) {
      return;
    }

    clear(legend);
    if (root.getAttribute('data-show-legend') !== 'true') {
      return;
    }

    values.forEach(function (row, index) {
      var item = htmlElement('span', 'chart__legend-item');
      var swatch = htmlElement('span', 'chart__legend-swatch');
      swatch.style.background = chartColors[index % chartColors.length];
      item.appendChild(swatch);
      item.appendChild(htmlElement('span', 'chart__legend-label', row.label || 'Value ' + (index + 1)));
      item.appendChild(htmlElement('span', 'chart__legend-value', formatValue(row.value)));
      legend.appendChild(item);
    });
  }

  function linePoints(values, width, height, padX, padY, bottom, scale) {
    var chartW = width - padX * 2;
    var chartH = height - padY - bottom;
    var max = scale && typeof scale.max === 'number' ? scale.max : Math.max.apply(null, values.map(function (row) { return row.value; }));
    var min = scale && typeof scale.min === 'number' ? scale.min : Math.min.apply(null, values.map(function (row) { return row.value; }));
    var range = max - min || 1;

    return values.map(function (row, index) {
      var x = padX + (values.length === 1 ? chartW / 2 : (index / (values.length - 1)) * chartW);
      var y = padY + chartH - ((row.value - min) / range) * chartH;
      return [x, y];
    });
  }

  function drawLineChart(svg, values, options) {
    var width = options.width || 640;
    var height = options.height || 300;
    var padX = options.padX || 42;
    var padY = options.padY || 28;
    var bottom = options.bottom || 42;
    var chartH = height - padY - bottom;
    var scale = null;
    if (options.yAxis) {
      scale = options.yAxisFromZero
        ? yScaleFromZero(values, options.yTickCount || 5)
        : yScale(values, options.yTickCount || 5);
    }
    var points = linePoints(values, width, height, padX, padY, bottom, scale);
    var pointList = points.map(function (point) {
      return point[0].toFixed(2) + ' ' + point[1].toFixed(2);
    }).join(' ');

    clear(svg);
    svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);

    if (options.yAxis && scale) {
      var axisRange = scale.max - scale.min || 1;
      scale.ticks.slice().reverse().forEach(function (tick) {
        var y = padY + chartH - ((tick - scale.min) / axisRange) * chartH;
        svg.appendChild(svgElement('line', {
          x1: padX,
          x2: width - padX,
          y1: y,
          y2: y,
          stroke: 'currentColor',
          'stroke-opacity': '0.1'
        }));
        var label = svgElement('text', {
          x: padX - (options.axisCompact ? 14 : 16),
          y: y + 4,
          'text-anchor': 'end',
          class: 'chart__axis-label'
        });
        label.textContent = options.axisCompact
          ? formatCompactAxisValue(tick)
          : formatAxisValue(tick, options.unit || '');
        svg.appendChild(label);
      });

      svg.appendChild(svgElement('line', {
        x1: padX,
        x2: padX,
        y1: padY,
        y2: height - bottom,
        stroke: 'currentColor',
        'stroke-opacity': '0.16'
      }));
    } else if (options.grid) {
      for (var i = 0; i <= 4; i++) {
        var gridY = padY + (chartH / 4) * i;
        svg.appendChild(svgElement('line', {
          x1: padX,
          x2: width - padX,
          y1: gridY,
          y2: gridY,
          stroke: 'currentColor',
          'stroke-opacity': '0.12'
        }));
      }
    }

    if (options.area) {
      var area = svgElement('polygon', {
        points: padX + ' ' + (height - bottom) + ' ' + pointList + ' ' + (width - padX) + ' ' + (height - bottom),
        fill: options.color || 'var(--chart-color, var(--primary))',
        opacity: String(options.areaOpacity || 0.14)
      });
      setAnimated(area, 0);
      svg.appendChild(area);
    }

    var line = svgElement('polyline', {
      points: pointList,
      fill: 'none',
      stroke: options.color || 'var(--chart-color, var(--primary))',
      'stroke-width': String(options.strokeWidth || 3),
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round'
    });
    setAnimated(line, 1);
    svg.appendChild(line);

    if (options.dots) {
      points.forEach(function (point, index) {
        var dot = svgElement('circle', {
          cx: point[0],
          cy: point[1],
          r: '4',
          fill: options.color || 'var(--chart-color, var(--primary))'
        });
        setAnimated(dot, index + 2);
        addTitle(dot, values[index].label + ': ' + values[index].value);
        svg.appendChild(dot);
      });
    }

    if (options.labels) {
      values.forEach(function (row, index) {
        var label = svgElement('text', {
          x: points[index][0],
          y: height - 10,
          'text-anchor': 'middle',
          fill: 'currentColor',
          class: 'chart__tick-label'
        });
        label.textContent = row.label;
        svg.appendChild(label);
      });
    }

    if (options.valueLabels) {
      values.forEach(function (row, index) {
        var valueLabel = svgElement('text', {
          x: points[index][0],
          y: Math.max(14, points[index][1] - 12),
          'text-anchor': 'middle',
          class: 'chart__value-label'
        });
        valueLabel.textContent = formatValue(row.value);
        svg.appendChild(valueLabel);
      });
    }
  }

  function drawGenericBarChart(svg, values, options) {
    var width = options.width || 640;
    var height = options.height || 300;
    var padX = options.padX || 42;
    var padY = options.padY || 28;
    var bottom = options.bottom || 50;
    var chartW = width - padX * 2;
    var chartH = height - padY - bottom;
    var max = Math.max.apply(null, values.map(function (row) { return row.value; })) || 1;
    var horizontal = options.orientation === 'horizontal';

    clear(svg);
    svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);

    if (options.grid) {
      for (var i = 0; i <= 4; i++) {
        var gridY = padY + (chartH / 4) * i;
        svg.appendChild(svgElement('line', {
          x1: padX,
          x2: width - padX,
          y1: gridY,
          y2: gridY,
          stroke: 'currentColor',
          'stroke-opacity': '0.12'
        }));
      }
    }

    if (horizontal) {
      var rowH = chartH / values.length;
      values.forEach(function (row, index) {
        var barWidth = (row.value / max) * chartW;
        var y = padY + index * rowH + rowH * 0.18;
        var rect = svgElement('rect', {
          x: padX,
          y: y,
          width: Math.max(barWidth, 1),
          height: rowH * 0.54,
          rx: '6',
          fill: chartColors[index % chartColors.length]
        });
        setAnimated(rect, index);
        addTitle(rect, row.label + ': ' + row.value);
        svg.appendChild(rect);

        var label = svgElement('text', {
          x: padX,
          y: y + rowH * 0.9,
          fill: 'currentColor',
          class: 'chart__tick-label'
        });
        label.textContent = row.label;
        svg.appendChild(label);

        if (options.valueLabels) {
          var valueLabel = svgElement('text', {
            x: Math.min(width - padX, padX + barWidth + 10),
            y: y + rowH * 0.45,
            'dominant-baseline': 'middle',
            class: 'chart__value-label'
          });
          valueLabel.textContent = formatValue(row.value);
          svg.appendChild(valueLabel);
        }
      });
      return;
    }

    var barW = (chartW / values.length) * 0.52;
    var gap = (chartW - barW * values.length) / Math.max(values.length - 1, 1);
    values.forEach(function (row, index) {
      var valueH = (row.value / max) * chartH;
      var x = padX + index * (barW + gap);
      var y = padY + chartH - valueH;
      var rect = svgElement('rect', {
        x: x,
        y: y,
        width: barW,
        height: Math.max(valueH, 1),
        rx: '6',
        fill: chartColors[index % chartColors.length]
      });
      setAnimated(rect, index);
      addTitle(rect, row.label + ': ' + row.value);
      svg.appendChild(rect);

      var label = svgElement('text', {
        x: x + barW / 2,
        y: height - 12,
        'text-anchor': 'middle',
        fill: 'currentColor',
        class: 'chart__tick-label'
      });
      label.textContent = row.label;
      svg.appendChild(label);

      if (options.valueLabels) {
        var valueLabel = svgElement('text', {
          x: x + barW / 2,
          y: Math.max(14, y - 10),
          'text-anchor': 'middle',
          class: 'chart__value-label'
        });
        valueLabel.textContent = formatValue(row.value);
        svg.appendChild(valueLabel);
      }
    });
  }

  function initLineChartRoots(selector, canvasSelector, buildOptions, animate) {
    each(selector, function (root) {
      if (!once(root)) {
        return;
      }
      var svg = root.querySelector(canvasSelector);
      var values = numericRows(parseJson(root, 'data-chart-data', []), false);
      if (!svg || !values.length) {
        return;
      }

      drawLineChart(svg, values, buildOptions(root, values));
      if (animate !== false) {
        animateChart(svg);
      }
    });
  }

  function initGenericLine() {
    each('.chart', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.chart__canvas');
      var values = numericRows(parseJson(root, 'data-chart-data', []), false);
      if (!svg || !values.length) return;
      var chartType = root.getAttribute('data-chart-type') || 'area';
      var showValues = root.getAttribute('data-show-values') === 'true';
      var isSeriesChart = chartType === 'area' || chartType === 'line';

      renderLegend(root, values);
      if (chartType === 'bar' || chartType === 'horizontal_bar') {
        drawGenericBarChart(svg, values, {
          grid: root.getAttribute('data-show-grid') === 'true',
          orientation: chartType === 'horizontal_bar' ? 'horizontal' : 'vertical',
          valueLabels: showValues
        });
      } else {
        drawLineChart(svg, values, {
          yAxis: true,
          yAxisFromZero: isSeriesChart,
          yTickCount: 5,
          unit: root.getAttribute('data-unit') || '',
          padX: isSeriesChart ? 64 : 90,
          padY: 30,
          bottom: 44,
          area: chartType === 'area' && root.getAttribute('data-fill-type') !== 'none',
          areaOpacity: isSeriesChart ? 0.18 : 0.14,
          axisCompact: isSeriesChart,
          color: 'var(--chart-color, var(--chart-1))',
          dots: !isSeriesChart && showValues,
          labels: true,
          valueLabels: showValues
        });
      }
      animateChart(svg);
    });
  }

  function initArea() {
    var opacityMap = { low: 0.08, medium: 0.18, high: 0.28 };

    initLineChartRoots('.chart-area', '.chart-area__canvas', function (root) {
      return {
        area: true,
        yAxis: true,
        yTickCount: 5,
        unit: root.getAttribute('data-unit') || '',
        padX: 90,
        padY: 30,
        bottom: 44,
        labels: true,
        areaOpacity: opacityMap[root.getAttribute('data-fill-opacity')] || opacityMap.medium
      };
    }, false);
  }

  function initLine() {
    initLineChartRoots('.chart-line', '.chart-line__canvas', function (root) {
      return {
        yAxis: true,
        yTickCount: 5,
        unit: root.getAttribute('data-unit') || '',
        padX: 90,
        padY: 30,
        bottom: 44,
        dots: root.getAttribute('data-show-dots') === 'true',
        labels: true
      };
    }, false);
  }

  function initMetricDashboard() {
    each('.metric-dashboard__chart', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.metric-dashboard__sparkline');
      var rows = parseJson(root, 'data-chart-data', []);
      var values = numericRows(rows, false);
      if (!values.length) {
        values = numericValues(rows).map(function (value, index) {
          return { label: String(index + 1), value: value };
        });
      }
      values = values.map(function (row, index) {
        return {
          label: row.label || String(index + 1),
          value: row.value
        };
      });
      if (!svg || !values.length) return;

      drawLineChart(svg, values, {
        width: 640,
        height: 230,
        padX: 82,
        padY: 30,
        bottom: 42,
        yAxis: true,
        yTickCount: 5,
        unit: root.getAttribute('data-chart-unit') || '',
        labels: true,
        area: true,
        color: 'var(--primary)',
        areaOpacity: 0.12
      });
    });
  }

  function initSparkline() {
    each('.chart-sparkline', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.chart-sparkline__svg');
      var rows = parseJson(root, 'data-chart-json', []);
      var values = numericRows(rows, false);
      if (!values.length) {
        values = numericValues(rows).map(function (value, index) {
          return { label: String(index + 1), value: value };
        });
      }
      values = values.map(function (row, index) {
        return {
          label: row.label || String(index + 1),
          value: row.value
        };
      });
      if (!svg || !values.length) return;

      drawLineChart(svg, values, {
        width: 420,
        height: 150,
        padX: 72,
        padY: 18,
        bottom: 34,
        yAxis: true,
        yTickCount: 4,
        unit: root.getAttribute('data-chart-unit') || '',
        labels: true,
        area: true,
        color: 'currentColor',
        areaOpacity: 0.12,
        strokeWidth: 2
      });
    });
  }

  function barFillOpacity(value, max) {
    if (!max) {
      return '1';
    }
    return String(Math.max(0.42, 0.42 + (value / max) * 0.58));
  }

  function initBar() {
    each('.chart-bar', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.chart-bar__canvas');
      var values = numericRows(parseJson(root, 'data-chart-data', []), false);
      if (!svg || !values.length) return;

      clear(svg);
      var width = 640;
      var height = 300;
      var orientation = root.getAttribute('data-orientation') || 'vertical';
      var padX = orientation === 'horizontal' ? 42 : 52;
      var padY = 30;
      var bottom = 38;
      var chartW = width - padX * 2;
      var chartH = height - padY - bottom;
      var max = Math.max.apply(null, values.map(function (row) { return row.value; })) || 1;
      var seriesName = root.getAttribute('data-series-name') || '';
      var unit = root.getAttribute('data-unit') || '';
      var barColor = 'var(--chart-color, var(--chart-1))';
      svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);

      if (orientation === 'vertical') {
        var scale = yScaleFromZero(values, 5);
        var axisRange = scale.max - scale.min || 1;
        scale.ticks.slice().reverse().forEach(function (tick) {
          var axisY = padY + chartH - ((tick - scale.min) / axisRange) * chartH;
          svg.appendChild(svgElement('line', {
            x1: padX,
            x2: width - padX,
            y1: axisY,
            y2: axisY,
            stroke: 'currentColor',
            'stroke-opacity': '0.1'
          }));
          var axisLabel = svgElement('text', {
            x: padX - 14,
            y: axisY + 4,
            'text-anchor': 'end',
            class: 'chart__axis-label'
          });
          axisLabel.textContent = formatCompactAxisValue(tick);
          svg.appendChild(axisLabel);
        });
        svg.appendChild(svgElement('line', {
          x1: padX,
          x2: padX,
          y1: padY,
          y2: height - bottom,
          stroke: 'currentColor',
          'stroke-opacity': '0.16'
        }));
      } else if (root.getAttribute('data-show-grid') === 'true') {
        for (var i = 0; i <= 4; i++) {
          var gridY = padY + (chartH / 4) * i;
          svg.appendChild(svgElement('line', {
            x1: padX,
            x2: width - padX,
            y1: gridY,
            y2: gridY,
            stroke: 'currentColor',
            'stroke-opacity': '0.12'
          }));
        }
      }

      if (orientation === 'horizontal') {
        var rowH = chartH / values.length;
        values.forEach(function (row, index) {
          var barWidth = (row.value / max) * chartW;
          var y = padY + index * rowH + rowH * 0.18;
          var horizontalRect = svgElement('rect', {
            x: padX,
            y: y,
            width: Math.max(barWidth, 1),
            height: rowH * 0.54,
            rx: '6',
            fill: barColor,
            opacity: barFillOpacity(row.value, max),
            'data-bar-index': String(index + 1)
          });
          addTitle(horizontalRect, 'Bar ' + (index + 1) + ', ' + row.label + (seriesName ? ', ' + seriesName : '') + ': ' + formatAxisValue(row.value, unit));
          svg.appendChild(horizontalRect);
          var label = svgElement('text', {
            x: padX,
            y: y + rowH * 0.82,
            fill: 'currentColor',
            class: 'chart__tick-label'
          });
          label.textContent = row.label;
          svg.appendChild(label);
        });
        return;
      }

      var barW = (chartW / values.length) * 0.56;
      var gap = (chartW - barW * values.length) / Math.max(values.length - 1, 1);
      var verticalScale = yScaleFromZero(values, 5);
      var verticalRange = verticalScale.max - verticalScale.min || 1;
      values.forEach(function (row, index) {
        var valueH = ((row.value - verticalScale.min) / verticalRange) * chartH;
        var x = padX + index * (barW + gap);
        var y = padY + chartH - valueH;
        var rect = svgElement('rect', {
          x: x,
          y: y,
          width: barW,
          height: Math.max(valueH, 1),
          rx: '6',
          fill: barColor,
          opacity: barFillOpacity(row.value, verticalScale.max),
          'data-bar-index': String(index + 1)
        });
        addTitle(rect, 'Bar ' + (index + 1) + ', ' + row.label + (seriesName ? ', ' + seriesName : '') + ': ' + formatAxisValue(row.value, unit));
        svg.appendChild(rect);
        var label = svgElement('text', {
          x: x + barW / 2,
          y: height - 14,
          'text-anchor': 'middle',
          fill: 'currentColor',
          class: 'chart__tick-label'
        });
        label.textContent = row.label;
        svg.appendChild(label);
      });
    });
  }

  function arcPath(cx, cy, innerRadius, outerRadius, start, end) {
    var largeArc = end - start > Math.PI ? 1 : 0;
    var outerStart = [cx + outerRadius * Math.cos(start), cy + outerRadius * Math.sin(start)];
    var outerEnd = [cx + outerRadius * Math.cos(end), cy + outerRadius * Math.sin(end)];
    var innerEnd = [cx + innerRadius * Math.cos(end), cy + innerRadius * Math.sin(end)];
    var innerStart = [cx + innerRadius * Math.cos(start), cy + innerRadius * Math.sin(start)];

    return [
      'M', outerStart[0].toFixed(2), outerStart[1].toFixed(2),
      'A', outerRadius, outerRadius, 0, largeArc, 1, outerEnd[0].toFixed(2), outerEnd[1].toFixed(2),
      'L', innerEnd[0].toFixed(2), innerEnd[1].toFixed(2),
      'A', innerRadius, innerRadius, 0, largeArc, 0, innerStart[0].toFixed(2), innerStart[1].toFixed(2),
      'Z'
    ].join(' ');
  }

  function piePath(cx, cy, radius, start, end) {
    var largeArc = end - start > Math.PI ? 1 : 0;
    var startPoint = [cx + radius * Math.cos(start), cy + radius * Math.sin(start)];
    var endPoint = [cx + radius * Math.cos(end), cy + radius * Math.sin(end)];

    return [
      'M', cx, cy,
      'L', startPoint[0].toFixed(2), startPoint[1].toFixed(2),
      'A', radius, radius, 0, largeArc, 1, endPoint[0].toFixed(2), endPoint[1].toFixed(2),
      'Z'
    ].join(' ');
  }

  function initPie() {
    each('.chart-pie', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.chart-pie__canvas');
      var values = numericRows(parseJson(root, 'data-chart-data', []), true);
      if (!svg || !values.length) return;

      clear(svg);
      var cx = 180;
      var cy = 180;
      var radius = 118;
      var total = values.reduce(function (sum, row) { return sum + row.value; }, 0) || 1;
      var start = -Math.PI / 2;
      svg.setAttribute('viewBox', '0 0 360 360');

      values.forEach(function (row, index) {
        var angle = (row.value / total) * Math.PI * 2;
        var end = start + angle;
        var colorIndex = (index % chartColors.length) + 1;
        var path = svgElement('path', {
          d: piePath(cx, cy, radius, start, end),
          fill: 'var(--chart-pie-' + colorIndex + ', ' + chartColors[index % chartColors.length] + ')'
        });
        addTitle(path, row.label + ': ' + row.value);
        svg.appendChild(path);

        if (root.getAttribute('data-show-legend') === 'true') {
          var mid = start + angle / 2;
          var label = svgElement('text', {
            x: (cx + 154 * Math.cos(mid)).toFixed(2),
            y: (cy + 154 * Math.sin(mid)).toFixed(2),
            'text-anchor': 'middle',
            'dominant-baseline': 'middle',
            fill: 'currentColor'
          });
          label.textContent = row.label;
          svg.appendChild(label);
        }

        start = end;
      });
    });
  }

  function initDonut() {
    each('[data-ce="chart-donut"]', function (root) {
      if (!once(root)) return;
      var canvas = root.querySelector('[data-chart-donut-canvas]');
      var values = numericRows(parseJson(root, 'data-chart-data', []), true);
      if (!canvas || !values.length) return;

      clear(canvas);
      var svg = svgElement('svg', {
        viewBox: '0 0 360 360',
        class: 'chart-donut__svg',
        role: 'img',
        'aria-hidden': 'true'
      });
      var total = values.reduce(function (sum, row) { return sum + row.value; }, 0) || 1;
      var start = -Math.PI / 2;

      values.forEach(function (row, index) {
        var angle = (row.value / total) * Math.PI * 2;
        var end = start + angle;
        var path = svgElement('path', {
          d: arcPath(180, 180, 78, 124, start, end),
          fill: chartColors[index % chartColors.length]
        });
        addTitle(path, row.label + ': ' + row.value);
        svg.appendChild(path);
        start = end;
      });

      var centerValue = root.getAttribute('data-center-value');
      var centerLabel = root.getAttribute('data-center-label');
      if (centerValue) {
        var value = svgElement('text', {
          x: '180',
          y: centerLabel ? '174' : '184',
          'text-anchor': 'middle',
          class: 'chart-donut__center-value'
        });
        value.textContent = centerValue;
        svg.appendChild(value);
      }
      if (centerLabel) {
        var label = svgElement('text', {
          x: '180',
          y: centerValue ? '204' : '184',
          'text-anchor': 'middle',
          class: 'chart-donut__center-label'
        });
        label.textContent = centerLabel;
        svg.appendChild(label);
      }

      canvas.appendChild(svg);
    });
  }

  function initRadar() {
    each('.chart-radar', function (root) {
      if (!once(root)) return;
      var plot = root.querySelector('.chart-radar__plot');
      var data = parseJson(root, 'data-chart-json', {});
      var labels = listValues(data.labels).map(function (label) { return String(label); });
      var values = numericValues(data.values);
      if (!plot || !labels.length || labels.length !== values.length) return;

      clear(plot);
      var cx = 200;
      var cy = 200;
      var radius = 150;
      var count = labels.length;
      var svg = svgElement('svg', { viewBox: '0 0 400 400', class: 'chart-radar__svg' });

      for (var ring = 1; ring <= 5; ring++) {
        var poly = [];
        for (var i = 0; i < count; i++) {
          var angle = -Math.PI / 2 + (i / count) * 2 * Math.PI;
          var rr = (radius * ring) / 5;
          poly.push((cx + rr * Math.cos(angle)).toFixed(2) + ' ' + (cy + rr * Math.sin(angle)).toFixed(2));
        }
        svg.appendChild(svgElement('polygon', {
          points: poly.join(' '),
          fill: 'none',
          stroke: 'currentColor',
          'stroke-opacity': '0.15',
          'stroke-width': '1'
        }));
      }

      for (var axis = 0; axis < count; axis++) {
        var axisAngle = -Math.PI / 2 + (axis / count) * 2 * Math.PI;
        svg.appendChild(svgElement('line', {
          x1: cx,
          y1: cy,
          x2: cx + radius * Math.cos(axisAngle),
          y2: cy + radius * Math.sin(axisAngle),
          stroke: 'currentColor',
          'stroke-opacity': '0.25'
        }));
        var label = svgElement('text', {
          x: cx + (radius + 18) * Math.cos(axisAngle),
          y: cy + (radius + 18) * Math.sin(axisAngle),
          'text-anchor': 'middle',
          'dominant-baseline': 'middle',
          fill: 'currentColor',
          class: 'chart-radar__label'
        });
        label.textContent = labels[axis];
        svg.appendChild(label);
      }

      var pointData = values.map(function (value, index) {
        var angle = -Math.PI / 2 + (index / count) * 2 * Math.PI;
        var clamped = Math.min(Math.max(value, 0), 5);
        var rr = (radius * clamped) / 5;
        return {
          x: cx + rr * Math.cos(angle),
          y: cy + rr * Math.sin(angle),
          labelX: cx + Math.max(34, rr - 22) * Math.cos(angle),
          labelY: cy + Math.max(34, rr - 22) * Math.sin(angle),
          value: value,
          label: labels[index]
        };
      });
      var points = pointData.map(function (point) {
        return point.x.toFixed(2) + ' ' + point.y.toFixed(2);
      });
      svg.appendChild(svgElement('polygon', {
        points: points.join(' '),
        fill: 'var(--primary)',
        'fill-opacity': String(parseFloat(root.getAttribute('data-fill-opacity') || '0.25') || 0.25),
        stroke: 'var(--primary)',
        'stroke-width': '2'
      }));

      pointData.forEach(function (point, index) {
        var labelText = Number(point.value).toFixed(1);
        var badgeWidth = 34;
        var badgeHeight = 18;
        var labelX = Math.min(Math.max(point.labelX, badgeWidth / 2 + 6), 400 - badgeWidth / 2 - 6);
        var labelY = Math.min(Math.max(point.labelY, badgeHeight / 2 + 6), 400 - badgeHeight / 2 - 6);
        var group = svgElement('g', {
          class: 'chart-radar__value-badge'
        });
        var rect = svgElement('rect', {
          x: (labelX - badgeWidth / 2).toFixed(2),
          y: (labelY - badgeHeight / 2).toFixed(2),
          width: String(badgeWidth),
          height: String(badgeHeight),
          rx: '9',
          class: 'chart-radar__value-bg'
        });
        var valueLabel = svgElement('text', {
          x: labelX.toFixed(2),
          y: (labelY + 3.5).toFixed(2),
          'text-anchor': 'middle',
          class: 'chart-radar__value-label'
        });
        valueLabel.textContent = labelText;
        addTitle(group, point.label + ': ' + labelText + ' / 5');
        group.appendChild(rect);
        group.appendChild(valueLabel);
        setAnimated(group, index + 1);
        svg.appendChild(group);
      });
      plot.appendChild(svg);
    });
  }

  function initStackedBar() {
    each('.chart-stacked-bar', function (root) {
      if (!once(root)) return;
      var plot = root.querySelector('.chart-stacked-bar__plot');
      var rows = parseJson(root, 'data-chart-json', []);
      if (!plot || !Array.isArray(rows) || !rows.length) return;

      var seriesLabels = (root.getAttribute('data-series-labels') || '').split(',').map(function (label) {
        return label.trim();
      }).filter(Boolean);
      var unit = root.getAttribute('data-chart-unit') || '';
      var totalsBySeries = [];
      rows.forEach(function (row) {
        (row.segments || []).map(Number).forEach(function (seg, index) {
          totalsBySeries[index] = (totalsBySeries[index] || 0) + (Number.isNaN(seg) ? 0 : seg);
        });
      });
      Array.prototype.forEach.call(root.querySelectorAll('.chart-stacked-bar__legend-item'), function (item, index) {
        item.style.setProperty('--stacked-legend-color', 'var(--stacked-color-' + ((index % 5) + 1) + ')');
        if (!item.querySelector('.chart-stacked-bar__legend-value') && totalsBySeries[index] != null) {
          item.appendChild(htmlElement('span', 'chart-stacked-bar__legend-value', formatAxisValue(totalsBySeries[index], unit)));
        }
      });

      clear(plot);
      var width = 720;
      var height = 390;
      var padLeft = 84;
      var padRight = 28;
      var padTop = 44;
      var bottom = 58;
      var chartW = width - padLeft - padRight;
      var chartH = height - padTop - bottom;
      var stackTotals = rows.map(function (row) {
        return (row.segments || []).reduce(function (sum, value) {
          return sum + Number(value);
        }, 0);
      });
      var maxStack = Math.max.apply(null, stackTotals) || 1;
      var scale = yScaleFromZero(stackTotals.map(function (value) {
        return { label: '', value: value };
      }), 5);
      var axisRange = scale.max - scale.min || 1;
      var barW = Math.min(76, (chartW / rows.length) * 0.42);
      var gap = (chartW - barW * rows.length) / Math.max(rows.length - 1, 1);
      var svg = svgElement('svg', { viewBox: '0 0 ' + width + ' ' + height, class: 'chart-stacked-bar__svg' });

      scale.ticks.slice().reverse().forEach(function (tick) {
        var y = padTop + chartH - ((tick - scale.min) / axisRange) * chartH;
        svg.appendChild(svgElement('line', {
          x1: padLeft,
          x2: width - padRight,
          y1: y,
          y2: y,
          stroke: 'currentColor',
          'stroke-opacity': '0.1'
        }));
        var label = svgElement('text', {
          x: padLeft - 12,
          y: y + 4,
          'text-anchor': 'end',
          class: 'chart-stacked-bar__axis-label'
        });
        label.textContent = formatAxisValue(tick, unit);
        svg.appendChild(label);
      });
      svg.appendChild(svgElement('line', {
        x1: padLeft,
        x2: padLeft,
        y1: padTop,
        y2: height - bottom,
        stroke: 'currentColor',
        'stroke-opacity': '0.18'
      }));
      svg.appendChild(svgElement('line', {
        x1: padLeft,
        x2: width - padRight,
        y1: height - bottom,
        y2: height - bottom,
        stroke: 'currentColor',
        'stroke-opacity': '0.18'
      }));

      rows.forEach(function (row, index) {
        var x = padLeft + index * (barW + gap);
        var yAcc = height - bottom;
        var total = stackTotals[index] || 0;
        (row.segments || []).map(Number).forEach(function (seg, segIndex) {
          var value = Number.isNaN(seg) ? 0 : seg;
          var segH = (value / axisRange) * chartH;
          yAcc -= segH;
          var rect = svgElement('rect', {
            x: x,
            y: yAcc,
            width: barW,
            height: Math.max(segH, 0.5),
            rx: '5',
            fill: 'var(--stacked-color-' + ((segIndex % 5) + 1) + ')'
          });
          addTitle(rect, (row.label || 'Category ' + (index + 1)) + ', ' + (seriesLabels[segIndex] || 'Series ' + (segIndex + 1)) + ': ' + formatAxisValue(value, unit));
          setAnimated(rect, index + segIndex);
          svg.appendChild(rect);
          if (segH > 25) {
            var segmentLabel = svgElement('text', {
              x: x + barW / 2,
              y: yAcc + segH / 2 + 3,
              'text-anchor': 'middle',
              class: 'chart-stacked-bar__segment-label'
            });
            segmentLabel.textContent = formatValue(value);
            svg.appendChild(segmentLabel);
          }
        });
        var totalY = padTop + chartH - ((total - scale.min) / axisRange) * chartH;
        var totalLabel = svgElement('text', {
          x: x + barW / 2,
          y: Math.max(14, totalY - 10),
          'text-anchor': 'middle',
          class: 'chart-stacked-bar__total-label'
        });
        totalLabel.textContent = formatAxisValue(total, unit);
        svg.appendChild(totalLabel);
        var label = svgElement('text', {
          x: x + barW / 2,
          y: height - 20,
          'text-anchor': 'middle',
          fill: 'currentColor',
          class: 'chart-stacked-bar__tick'
        });
        label.textContent = row.label || '';
        svg.appendChild(label);
      });
      plot.appendChild(svg);
    });
  }

  function initHeatmap() {
    each('.chart-heatmap', function (root) {
      if (!once(root)) return;
      var plot = root.querySelector('.chart-heatmap__plot');
      var data = parseJson(root, 'data-chart-json', {});
      var rows = data.rows || [];
      var cols = data.cols || [];
      var values = data.values || [];
      if (!plot || !rows.length || !cols.length || !values.length) return;

      clear(plot);
      var flat = values.reduce(function (all, row) { return all.concat(row); }, []).map(Number);
      var vmax = Math.max.apply(null, flat);
      var vmin = Math.min.apply(null, flat);
      var span = vmax - vmin || 1;
      var wrap = htmlElement('div', 'chart-heatmap__grid');
      wrap.style.gridTemplateColumns = 'auto repeat(' + cols.length + ', minmax(2.5rem, 1fr))';
      wrap.appendChild(htmlElement('div', 'chart-heatmap__corner'));
      cols.forEach(function (column) {
        wrap.appendChild(htmlElement('div', 'chart-heatmap__colhead', column));
      });
      rows.forEach(function (rowLabel, rowIndex) {
        wrap.appendChild(htmlElement('div', 'chart-heatmap__rowhead', rowLabel));
        cols.forEach(function (_, colIndex) {
          var value = values[rowIndex] && values[rowIndex][colIndex] != null ? Number(values[rowIndex][colIndex]) : 0;
          var bucket = Math.max(0, Math.min(5, Math.round(((value - vmin) / span) * 5)));
          var cell = htmlElement('div', 'chart-heatmap__cell chart-heatmap__cell--' + bucket);
          // Value sits in its own chip so it stays legible on every --chart-N
          // fill in every preset (the fills vary from pastel to saturated).
          cell.appendChild(htmlElement('span', 'chart-heatmap__value', String(value)));
          cell.title = rowLabel + ' / ' + cols[colIndex] + ': ' + value;
          wrap.appendChild(cell);
        });
      });
      plot.appendChild(wrap);
    });
  }

  function initContribution() {
    each('.chart-contribution', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.chart-contribution__svg');
      var rows = numericRows(parseJson(root, 'data-chart-json', []), false);
      if (!rows.length) {
        rows = Array.prototype.map.call(root.querySelectorAll('tbody tr'), function (row) {
          var label = row.querySelector('th');
          var value = row.querySelector('td');
          return {
            label: label ? label.textContent.trim() : '',
            value: value ? Number(value.textContent.replace(/[^0-9.-]/g, '')) : NaN
          };
        }).filter(function (row) {
          return row.label && !Number.isNaN(row.value);
        });
      }
      if (!svg || !rows.length) return;

      clear(svg);
      var values = rows.map(function (row) { return row.value; });
      var labels = rows.map(function (row) { return row.label; });
      var unit = root.getAttribute('data-unit') || '';
      var width = 640;
      var height = 220;
      var padX = 52;
      var padY = 24;
      var bottom = 30;
      var chartW = width - padX * 2;
      var chartH = height - padY - bottom;
      var scale = yScale(rows, 5);
      var axisRange = scale.max - scale.min || 1;
      var barW = (chartW / values.length) * 0.52;
      var gap = (chartW - barW * values.length) / Math.max(values.length - 1, 1);
      var barColor = 'var(--chart-color, var(--chart-1))';
      svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);

      scale.ticks.slice().reverse().forEach(function (tick) {
        var axisY = padY + chartH - ((tick - scale.min) / axisRange) * chartH;
        svg.appendChild(svgElement('line', {
          x1: padX,
          x2: width - padX,
          y1: axisY,
          y2: axisY,
          stroke: 'currentColor',
          'stroke-opacity': '0.1'
        }));
        var axisLabel = svgElement('text', {
          x: padX - 10,
          y: axisY + 3,
          'text-anchor': 'end',
          class: 'chart-contribution__axis-label',
          'font-size': '7'
        });
        axisLabel.textContent = formatCompactAxisValue(tick) + (unit ? ' ' + unit : '');
        svg.appendChild(axisLabel);
      });

      svg.appendChild(svgElement('line', {
        x1: padX,
        x2: padX,
        y1: padY,
        y2: height - bottom,
        stroke: 'currentColor',
        'stroke-opacity': '0.16'
      }));

      values.forEach(function (value, index) {
        var valueH = ((value - scale.min) / axisRange) * chartH;
        var x = padX + index * (barW + gap);
        var y = padY + chartH - valueH;
        svg.appendChild(svgElement('rect', {
          x: x,
          y: y,
          width: barW,
          height: Math.max(valueH, 1),
          rx: '6',
          fill: barColor,
          opacity: barFillOpacity(value, Math.max.apply(null, values))
        }));
        var label = svgElement('text', {
          x: x + barW / 2,
          y: height - 10,
          'text-anchor': 'middle',
          class: 'chart-contribution__tick',
          'font-size': '8'
        });
        label.textContent = labels[index];
        svg.appendChild(label);
      });
    });
  }

  function initAll() {
    initGenericLine();
    initArea();
    initLine();
    initBar();
    initPie();
    initDonut();
    initRadar();
    initSparkline();
    initStackedBar();
    initHeatmap();
    initContribution();
    initMetricDashboard();
  }

  window.DesiderioCharts = { init: initAll };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }
}());
