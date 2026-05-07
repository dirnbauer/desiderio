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

  function numericRows(rows, positiveOnly) {
    if (!Array.isArray(rows)) {
      return [];
    }

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
    if (!Array.isArray(rows)) {
      return [];
    }

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

  function linePoints(values, width, height, padX, padY, bottom) {
    var chartW = width - padX * 2;
    var chartH = height - padY - bottom;
    var max = Math.max.apply(null, values.map(function (row) { return row.value; }));
    var min = Math.min.apply(null, values.map(function (row) { return row.value; }));
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
    var points = linePoints(values, width, height, padX, padY, bottom);
    var pointList = points.map(function (point) {
      return point[0].toFixed(2) + ' ' + point[1].toFixed(2);
    }).join(' ');

    clear(svg);
    svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);

    if (options.grid) {
      for (var i = 0; i <= 4; i++) {
        var y = padY + (chartH / 4) * i;
        svg.appendChild(svgElement('line', {
          x1: padX,
          x2: width - padX,
          y1: y,
          y2: y,
          stroke: 'currentColor',
          'stroke-opacity': '0.12'
        }));
      }
    }

    if (options.area) {
      svg.appendChild(svgElement('polygon', {
        points: padX + ' ' + (height - bottom) + ' ' + pointList + ' ' + (width - padX) + ' ' + (height - bottom),
        fill: options.color || 'var(--chart-color, var(--primary))',
        opacity: String(options.areaOpacity || 0.14)
      }));
    }

    svg.appendChild(svgElement('polyline', {
      points: pointList,
      fill: 'none',
      stroke: options.color || 'var(--chart-color, var(--primary))',
      'stroke-width': String(options.strokeWidth || 3),
      'stroke-linecap': 'round',
      'stroke-linejoin': 'round'
    }));

    if (options.dots) {
      points.forEach(function (point, index) {
        var dot = svgElement('circle', {
          cx: point[0],
          cy: point[1],
          r: '4',
          fill: options.color || 'var(--chart-color, var(--primary))'
        });
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
          opacity: '0.72'
        });
        label.textContent = row.label;
        svg.appendChild(label);
      });
    }
  }

  function initGenericLine() {
    each('.chart', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.chart__canvas');
      var values = numericRows(parseJson(root, 'data-chart-data', []), false);
      if (!svg || !values.length) return;

      drawLineChart(svg, values, {
        grid: root.getAttribute('data-show-grid') === 'true',
        area: root.getAttribute('data-fill-type') !== 'none',
        dots: true
      });
    });
  }

  function initArea() {
    each('.chart-area', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.chart-area__canvas');
      var values = numericRows(parseJson(root, 'data-chart-data', []), false);
      if (!svg || !values.length) return;
      var opacityMap = { low: 0.08, medium: 0.18, high: 0.28 };

      drawLineChart(svg, values, {
        area: true,
        labels: true,
        areaOpacity: opacityMap[root.getAttribute('data-fill-opacity')] || opacityMap.medium
      });
    });
  }

  function initLine() {
    each('.chart-line', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.chart-line__canvas');
      var values = numericRows(parseJson(root, 'data-chart-data', []), false);
      if (!svg || !values.length) return;

      drawLineChart(svg, values, {
        dots: root.getAttribute('data-show-dots') === 'true',
        labels: true
      });
    });
  }

  function initMetricDashboard() {
    each('.metric-dashboard__chart', function (root) {
      if (!once(root)) return;
      var svg = root.querySelector('.metric-dashboard__sparkline');
      var rows = parseJson(root, 'data-chart-data', []);
      var values = numericValues(rows).map(function (value) {
        return { label: '', value: value };
      });
      if (!svg || !values.length) return;

      drawLineChart(svg, values, {
        width: 640,
        height: 150,
        padX: 10,
        padY: 10,
        bottom: 10,
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
      var values = numericValues(parseJson(root, 'data-chart-json', [])).map(function (value) {
        return { label: '', value: value };
      });
      if (!svg || !values.length) return;

      drawLineChart(svg, values, {
        width: 400,
        height: 56,
        padX: 4,
        padY: 4,
        bottom: 4,
        area: true,
        color: 'currentColor',
        areaOpacity: 0.12,
        strokeWidth: 2
      });
    });
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
      var padX = 42;
      var padY = 28;
      var bottom = 48;
      var chartW = width - padX * 2;
      var chartH = height - padY - bottom;
      var max = Math.max.apply(null, values.map(function (row) { return row.value; })) || 1;
      var orientation = root.getAttribute('data-orientation') || 'vertical';
      svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);

      if (root.getAttribute('data-show-grid') === 'true') {
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
          svg.appendChild(svgElement('rect', {
            x: padX,
            y: y,
            width: Math.max(barWidth, 1),
            height: rowH * 0.54,
            rx: '6',
            fill: 'var(--chart-color, var(--primary))'
          }));
          var label = svgElement('text', {
            x: padX,
            y: y + rowH * 0.82,
            fill: 'currentColor',
            opacity: '0.72'
          });
          label.textContent = row.label;
          svg.appendChild(label);
        });
        return;
      }

      var barW = (chartW / values.length) * 0.56;
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
          fill: 'var(--chart-color, var(--primary))'
        });
        addTitle(rect, row.label + ': ' + row.value);
        svg.appendChild(rect);
        var label = svgElement('text', {
          x: x + barW / 2,
          y: height - 12,
          'text-anchor': 'middle',
          fill: 'currentColor',
          opacity: '0.72'
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
        var path = svgElement('path', {
          d: piePath(cx, cy, radius, start, end),
          fill: chartColors[index % chartColors.length]
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
            fill: 'currentColor',
            opacity: '0.78'
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
      var labels = data.labels || [];
      var values = (data.values || []).map(function (value) { return Number(value); });
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

      var points = values.map(function (value, index) {
        var angle = -Math.PI / 2 + (index / count) * 2 * Math.PI;
        var clamped = Math.min(Math.max(value, 0), 5);
        var rr = (radius * clamped) / 5;
        return (cx + rr * Math.cos(angle)).toFixed(2) + ' ' + (cy + rr * Math.sin(angle)).toFixed(2);
      });
      svg.appendChild(svgElement('polygon', {
        points: points.join(' '),
        fill: 'var(--primary)',
        'fill-opacity': String(parseFloat(root.getAttribute('data-fill-opacity') || '0.25') || 0.25),
        stroke: 'var(--primary)',
        'stroke-width': '2'
      }));
      plot.appendChild(svg);
    });
  }

  function initStackedBar() {
    each('.chart-stacked-bar', function (root) {
      if (!once(root)) return;
      var plot = root.querySelector('.chart-stacked-bar__plot');
      var rows = parseJson(root, 'data-chart-json', []);
      if (!plot || !Array.isArray(rows) || !rows.length) return;

      clear(plot);
      var width = 400;
      var height = 220;
      var pad = 28;
      var bottom = 36;
      var chartH = height - pad - bottom;
      var maxStack = Math.max.apply(null, rows.map(function (row) {
        return (row.segments || []).reduce(function (sum, value) {
          return sum + Number(value);
        }, 0);
      })) || 1;
      var barW = (width - 2 * pad) / rows.length * 0.55;
      var gap = (width - 2 * pad - barW * rows.length) / Math.max(rows.length - 1, 1);
      var svg = svgElement('svg', { viewBox: '0 0 ' + width + ' ' + height, class: 'chart-stacked-bar__svg' });

      if (root.getAttribute('data-show-legend') === '1') {
        var legend = htmlElement('div', 'chart-stacked-bar__legend');
        var maxSeg = Math.max.apply(null, rows.map(function (row) { return (row.segments || []).length; }));
        for (var j = 0; j < maxSeg; j++) {
          var item = htmlElement('span', 'chart-stacked-bar__legend-item');
          item.appendChild(htmlElement('span', 'chart-stacked-bar__swatch chart-stacked-bar__swatch--' + ((j % 5) + 1)));
          item.appendChild(document.createTextNode('S' + (j + 1)));
          legend.appendChild(item);
        }
        plot.appendChild(legend);
      }

      rows.forEach(function (row, index) {
        var x = pad + index * (barW + gap);
        var yAcc = height - bottom;
        (row.segments || []).map(Number).forEach(function (seg, segIndex) {
          var segH = (seg / maxStack) * chartH;
          yAcc -= segH;
          svg.appendChild(svgElement('rect', {
            x: x,
            y: yAcc,
            width: barW,
            height: Math.max(segH, 0.5),
            rx: '4',
            fill: chartColors[segIndex % chartColors.length]
          }));
        });
        var label = svgElement('text', {
          x: x + barW / 2,
          y: height - 6,
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
          var cell = htmlElement('div', 'chart-heatmap__cell chart-heatmap__cell--' + bucket, String(value));
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
      var rows = parseJson(root, 'data-chart-json', []);
      if (!svg || !Array.isArray(rows) || !rows.length) return;

      clear(svg);
      var values = rows.map(function (row) { return Number(row.value) || 0; });
      var labels = rows.map(function (row) { return row.label || ''; });
      var width = 400;
      var height = 160;
      var pad = 12;
      var bottom = 28;
      var chartH = height - pad - bottom;
      var max = Math.max.apply(null, values) || 1;
      var barW = (width - 2 * pad) / values.length * 0.55;
      var gap = (width - 2 * pad - barW * values.length) / Math.max(values.length - 1, 1);
      svg.setAttribute('viewBox', '0 0 ' + width + ' ' + height);

      values.forEach(function (value, index) {
        var x = pad + index * (barW + gap);
        var barH = (value / max) * chartH;
        var y = height - bottom - barH;
        svg.appendChild(svgElement('rect', {
          x: x,
          y: y,
          width: barW,
          height: Math.max(barH, 1),
          rx: '6',
          fill: 'var(--chart-2)'
        }));
        var label = svgElement('text', {
          x: x + barW / 2,
          y: height - 4,
          'text-anchor': 'middle',
          fill: 'currentColor',
          class: 'chart-contribution__tick'
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
