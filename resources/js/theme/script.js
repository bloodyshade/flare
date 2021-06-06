document.addEventListener("DOMContentLoaded", () => {
  // Sortable
  let element = null;

  element = document.getElementById("sortable-style-1");
  if (element) {
    const sortable = Sortable.create(element, {
      animation: 150,
    });
  }

  element = document.getElementById("sortable-style-2");
  if (element) {
    const sortable = Sortable.create(element, {
      handle: ".handle",
      animation: 150,
    });
  }

  element = document.getElementById("sortable-style-3");
  if (element) {
    const sortable = Sortable.create(element, {
      animation: 150,
    });
  }

  // Editors
  // CKEditor
  const editor = document.getElementById("ckeditor");
  if (editor) {
    ClassicEditor.create(editor);
  }

  // Carousel
  const carousel = document.getElementById("carousel-style-1");
  if (carousel) {
    new Glide(carousel, {
      type: "carousel",
      perView: 4,
      gap: 20,
      breakpoints: {
        640: {
          perView: 1,
        },
        768: {
          perView: 2,
        },
      },
    }).mount();
  }
});

// Event delegation
const on = (selector, eventType, childSelector, eventHandler) => {
  const elements = document.querySelectorAll(selector);
  for (element of elements) {
    element.addEventListener(eventType, (eventOnElement) => {
      if (eventOnElement.target.closest(childSelector)) {
        eventHandler(eventOnElement);
      }
    });
  }
};

// AnimateCSS
const animateCSS = (element, animation, prefix = "animate__") => {
  return new Promise((resolve, reject) => {
    const animationName = `${prefix}${animation}`;
    const node = element;

    node.classList.add(`${prefix}animated`, `${prefix}faster`, animationName);

    const handleAnimationEnd = (event) => {
      event.stopPropagation();
      node.classList.remove(
        `${prefix}animated`,
        `${prefix}faster`,
        animationName
      );
      resolve("Animation Ended.");
    };

    node.addEventListener("animationend", handleAnimationEnd, { once: true });
  });
};

// Viewport Width
// Define our viewportWidth variable
let viewportWidth;

// Set/update the viewportWidth value
const setViewportWidth = () => {
  viewportWidth = window.innerWidth || document.documentElement.clientWidth;
};

// Watch the viewport width
const watchWidth = () => {
  const sm = 640;
  const md = 768;
  const lg = 1024;
  const xl = 1280;

  const menuBar = document.querySelector(".menu-bar");

  // Hide Menu Detail
  const hideMenuDetail = () => {
    menuBar.querySelectorAll(".menu-detail.open").forEach((menuDetail) => {
      hideOverlay();

      if (!menuBar.classList.contains("menu-wide")) {
        menuDetail.classList.remove("open");
      }
    });
  };

  // Hide Sidebar
  const hideSidebar = () => {
    const sidebar = document.querySelector(".sidebar");

    if (!sidebar) return;

    if (sidebar.classList.contains("open")) {
      sidebar.classList.remove("open");
      hideOverlay();
    }
  };

  if (viewportWidth < sm) {
    if (!menuBar) return;

    const openMenu = menuBar.querySelector(".menu-detail.open");

    if (!openMenu) {
      menuBar.classList.add("menu-hidden");
      document.documentElement.classList.add("menu-hidden");
      hideMenuDetail();
    }
  }

  if (viewportWidth > sm) {
    if (!menuBar) return;

    menuBar.classList.remove("menu-hidden");
    document.documentElement.classList.remove("menu-hidden");
  }

  if (viewportWidth > lg) {
    hideSidebar();
  }
};

// Set our initial width
setViewportWidth();
watchWidth();

// On resize events, recalculate
window.addEventListener(
  "resize",
  () => {
    setViewportWidth();
    watchWidth();
  },
  false
);

// Open Collapse
const openCollapse = (collapse, callback) => {
  collapse.style.transitionProperty = "height, opacity";
  collapse.style.transitionDuration = "200ms";
  collapse.style.transitionTimingFunction = "ease-in-out";

  setTimeout(() => {
    collapse.style.height = collapse.scrollHeight + "px";
    collapse.style.opacity = 1;
  }, 200);

  collapse.addEventListener(
    "transitionend",
    () => {
      collapse.classList.add("open");

      collapse.style.removeProperty("height");
      collapse.style.removeProperty("opacity");

      collapse.style.removeProperty("transition-property");
      collapse.style.removeProperty("transition-duration");
      collapse.style.removeProperty("transition-timing-function");

      if (typeof callback === "function") callback();
    },
    { once: true }
  );
};

// Close Collapse
const closeCollapse = (collapse, callback) => {
  collapse.style.overflowY = "hidden";
  collapse.style.height = collapse.scrollHeight + "px";

  collapse.style.transitionProperty = "height, opacity";
  collapse.style.transitionDuration = "200ms";
  collapse.style.transitionTimingFunction = "ease-in-out";

  setTimeout(() => {
    collapse.style.height = 0;
    collapse.style.opacity = 0;
  }, 200);

  collapse.addEventListener(
    "transitionend",
    () => {
      collapse.classList.remove("open");

      collapse.style.removeProperty("overflow-y");
      collapse.style.removeProperty("height");
      collapse.style.removeProperty("opacity");

      collapse.style.removeProperty("transition-property");
      collapse.style.removeProperty("transition-duration");
      collapse.style.removeProperty("transition-timing-function");

      if (typeof callback === "function") callback();
    },
    { once: true }
  );
};

// Alerts
const alerts = () => {
  // Close
  const closeAlert = (alert) => {
    alert.style.overflowY = "hidden";
    alert.style.height = alert.offsetHeight + "px";

    animateCSS(alert, "fadeOut").then(() => {
      alert.style.transitionProperty =
        "height, margin, padding, border, opacity";
      alert.style.transitionDuration = "200ms";
      alert.style.transitionTimingFunction = "linear";

      alert.style.opacity = 0;
      alert.style.height = 0;
      alert.style.marginTop = 0;
      alert.style.marginBottom = 0;
      alert.style.paddingTop = 0;
      alert.style.paddingBottom = 0;
      alert.style.border = 0;
    });

    alert.addEventListener(
      "transitionend",
      () => {
        alert.parentNode ? alert.parentNode.removeChild(alert) : false;
      },
      { once: true }
    );
  };

  on(".alert", "click", '[data-dismiss="alert"]', (event) => {
    const alert = event.target.closest(".alert");
    closeAlert(alert);
  });
};

alerts();

// Cards
const cards = () => {
  // Toggle Card Selection
  const toggleCardSelection = (event) => {
    const card = event.target.closest(".card");
    card.classList.toggle("card_selected");
  };

  on("body", "click", '[data-toggle="cardSelection"]', (event) => {
    toggleCardSelection(event);
  });

  // Toggle Row Selection
  const toggleRowSelection = (event) => {
    const row = event.target.closest("tr");
    row.classList.toggle("row_selected");
  };

  on("body", "click", '[data-toggle="rowSelection"]', (event) => {
    toggleRowSelection(event);
  });
};

cards();

if (typeof Chart !== "undefined") {
  // Colors
  let colors = {};
  colors.primary = "20, 83, 136";

  // Chart defaults
  Chart.defaults.color = "#555555";
  Chart.defaults.font.family = "'Nunito Sans', sans-serif";

  // Line with shadow element
  class LineWithShadowElement extends Chart.elements.LineElement {
    draw(ctx) {
      const originalStroke = ctx.stroke;

      ctx.stroke = function () {
        ctx.save();
        ctx.shadowColor = "rgba(0, 0, 0, 0.25)";
        ctx.shadowBlur = 8;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 4;
        originalStroke.apply(this, arguments);
        ctx.restore();
      };

      Chart.elements.LineElement.prototype.draw.apply(this, arguments);
    }
  }

  LineWithShadowElement.id = "lineWithShadowElement";

  Chart.register(LineWithShadowElement);

  // Line with shadow
  class LineWithShadow extends Chart.controllers.line {}

  LineWithShadow.id = "lineWithShadow";
  LineWithShadow.defaults = {
    datasetElementType: "lineWithShadowElement",
  };

  Chart.register(LineWithShadow);

  // Bar with shadow
  class BarWithShadow extends Chart.controllers.bar {
    draw(ease) {
      const ctx = this.chart.ctx;

      Chart.controllers.bar.prototype.draw.call(this, ease);
      ctx.save();
      ctx.shadowColor = "rgba(0, 0, 0, 0.25)";
      ctx.shadowBlur = 8;
      ctx.shadowOffsetX = 0;
      ctx.shadowOffsetY = 4;
      Chart.controllers.bar.prototype.draw.apply(this, arguments);
      ctx.restore();
    }
  }

  BarWithShadow.id = "barWithShadow";

  Chart.register(BarWithShadow);

  // Pie with shadow
  class PieWithShadow extends Chart.controllers.pie {}

  PieWithShadow.id = "pieWithShadow";
  PieWithShadow.defaults = {
    datasetElementType: "lineWithShadowElement",
  };

  Chart.register(PieWithShadow);

  // Doughnut with shadow
  class DoughnutWithShadow extends Chart.controllers.doughnut {}

  DoughnutWithShadow.id = "doughnutWithShadow";
  DoughnutWithShadow.defaults = {
    datasetElementType: "lineWithShadowElement",
  };

  Chart.register(DoughnutWithShadow);

  // Radar with shadow
  class RadarWithShadow extends Chart.controllers.radar {}

  RadarWithShadow.id = "radarWithShadow";
  RadarWithShadow.defaults = {
    datasetElementType: "lineWithShadowElement",
  };

  Chart.register(RadarWithShadow);

  // PolarArea with shadow
  class PolarAreaWithShadow extends Chart.controllers.polarArea {}

  PolarAreaWithShadow.id = "polarAreaWithShadow";
  PolarAreaWithShadow.defaults = {
    datasetElementType: "lineWithShadowElement",
  };

  Chart.register(PolarAreaWithShadow);

  // Line with annotation
  class LineWithAnnotation extends Chart.controllers.line {
    draw(ease) {
      const ctx = this.chart.ctx;

      Chart.controllers.line.prototype.draw.call(this, ease);

      if (this.chart.tooltip._active && this.chart.tooltip._active.length) {
        const activePoint = this.chart.tooltip._active[0];
        const x = activePoint.element.x;
        const topY = this.chart.scales["y"].top;
        const bottomY = this.chart.scales["y"].bottom;

        ctx.save();
        ctx.beginPath();
        ctx.moveTo(x, topY);
        ctx.lineTo(x, bottomY);
        ctx.lineWidth = 1;
        ctx.strokeStyle = "rgba(0, 0, 0, 0.1)";
        ctx.stroke();
        ctx.restore();
      }
    }
  }

  LineWithAnnotation.id = "lineWithAnnotation";

  Chart.register(LineWithAnnotation);

  // Line with annotation and shadow
  class LineWithAnnotationAndShadow extends Chart.controllers.line {
    draw(ease) {
      const ctx = this.chart.ctx;

      Chart.controllers.line.prototype.draw.call(this, ease);

      if (this.chart.tooltip._active && this.chart.tooltip._active.length) {
        const activePoint = this.chart.tooltip._active[0];
        const x = activePoint.element.x;
        const topY = this.chart.scales["y"].top;
        const bottomY = this.chart.scales["y"].bottom;

        ctx.save();
        ctx.beginPath();
        ctx.moveTo(x, topY);
        ctx.lineTo(x, bottomY);
        ctx.lineWidth = 1;
        ctx.strokeStyle = "rgba(0, 0, 0, 0.1)";
        ctx.stroke();
        ctx.restore();
      }
    }
  }

  LineWithAnnotationAndShadow.id = "lineWithAnnotationAndShadow";
  LineWithAnnotationAndShadow.defaults = {
    datasetElementType: "lineWithShadowElement",
  };

  Chart.register(LineWithAnnotationAndShadow);
}

// Collapse
const collapse = () => {
  const selector = '[data-toggle="collapse"]';

  // Toggle Collapse
  const toggleCollapse = (collapseTrigger) => {
    collapseTrigger.classList.toggle("active");

    // Collapse
    const collapses = document.querySelectorAll(collapseTrigger.dataset.target);
    collapses.forEach((collapse) => {
      if (collapse.classList.contains("open")) {
        closeCollapse(collapse);
      } else {
        openCollapse(collapse);
      }
    });

    // Accordion
    const accordion = collapseTrigger.closest(".accordion");
    if (accordion) {
      const accordionTriggers = accordion.querySelectorAll(selector);
      accordionTriggers.forEach((accordionTrigger) => {
        if (accordionTrigger !== collapseTrigger) {
          accordionTrigger.classList.remove("active");
        }
      });

      const accordions = accordion.querySelectorAll(".collapse");
      accordions.forEach((accordion) => {
        if (accordion.classList.contains("open")) {
          closeCollapse(accordion);
        }
      });
    }
  };

  on("body", "click", selector, (event) => {
    const collapseTrigger = event.target.closest(selector);
    toggleCollapse(collapseTrigger);
  });
};

collapse();

// Dark Mode
const darkMode = () => {
  const root = document.documentElement;

  const scheme = localStorage.getItem("scheme");

  const darkModeToggler = document.getElementById("darkModeToggler");

  scheme && root.classList.add(scheme);

  if (!darkModeToggler) return;

  if (scheme === "dark") {
    darkModeToggler.checked = "checked";
  }

  // Enable Dark Mode
  const enableDarkMode = () => {
    root.classList.remove("light");
    root.classList.add("dark");
    localStorage.setItem("scheme", "dark");
  };

  // Disable Dark Mode
  const disableDarkMode = () => {
    root.classList.remove("dark");
    root.classList.add("light");
    localStorage.removeItem("scheme");
  };

  // Check Dark Mode
  const checkDarkMode = () => {
    if (root.classList.contains("dark")) {
      return true;
    } else {
      return false;
    }
  };

  // Toggler
  darkModeToggler.addEventListener("change", () => {
    if (checkDarkMode()) {
      disableDarkMode();
    } else {
      enableDarkMode();
    }
  });
};

darkMode();

if (typeof Chart !== "undefined") {
  // Colors
  let colors = {};
  colors.primary = "20, 83, 136";

  // Tooltips Options
  const tooltipOptions = {
    backgroundColor: "#ffffff",
    borderColor: "#dddddd",
    borderWidth: 0.5,
    bodyColor: "#555555",
    bodySpacing: 8,
    cornerRadius: 4,
    padding: 16,
    titleColor: "rgba(" + colors.primary + ")",
  };

  // CHARTS
  let ctx = "";

  // DASHBOARD
  // Visitors chart
  ctx = document.getElementById("visitorsChart");
  if (ctx) {
    ctx = ctx.getContext("2d");

    let gradientBackground = ctx.createLinearGradient(0, 0, 0, 450);
    gradientBackground.addColorStop(0, "rgba(" + colors.primary + ", .5)");
    gradientBackground.addColorStop(0.75, "rgba(" + colors.primary + ", 0)");

    new Chart(ctx, {
      type: "lineWithShadow",
      data: {
        labels: [
          "Jan",
          "Feb",
          "Mar",
          "Apr",
          "May",
          "Jun",
          "Jul",
          "Aug",
          "Sep",
          "Oct",
          "Nov",
          "Dec",
        ],
        datasets: [
          {
            data: [6.25, 7.5, 10, 7.5, 10, 12.5, 10, 12.5, 10, 12.5, 15, 16.25],
            backgroundColor: "rgba(" + colors.primary + ", .1)",
            // backgroundColor: gradientBackground,
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            fill: true,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverBorderWidth: 2,
            pointHoverRadius: 6,
            tension: 0.5,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            display: false,
          },
          tooltip: tooltipOptions,
        },
        scales: {
          y: {
            grid: {
              display: true,
              drawBorder: false,
            },
            min: 0,
            max: 20,
            ticks: {
              stepSize: 5,
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Categories chart
  ctx = document.getElementById("categoriesChart");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "polarAreaWithShadow",
      data: {
        labels: ["Potatoes", "Tomatoes", "Onions"],
        datasets: [
          {
            data: [25, 10, 15],
            backgroundColor: [
              "rgba(" + colors.primary + ", .1)",
              "rgba(" + colors.primary + ", .5)",
              "rgba(" + colors.primary + ", .25)",
            ],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
        scales: {
          r: {
            ticks: {
              display: false,
            },
          },
        },
        layout: {
          padding: 5,
        },
      },
    });
  }

  // CHARTS
  // Area chart
  ctx = document.getElementById("areaChart");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "line",
      data: {
        labels: ["January", "February", "March", "April", "May", "June"],
        datasets: [
          {
            data: [5, 10, 15, 10, 15, 10],
            backgroundColor: "rgba(" + colors.primary + ", .1)",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            fill: true,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverBorderWidth: 2,
            pointHoverRadius: 6,
            tension: 0.5,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            display: false,
          },
          tooltip: tooltipOptions,
        },
        scales: {
          y: {
            grid: {
              display: true,
              drawBorder: false,
            },
            min: 0,
            max: 20,
            ticks: {
              stepSize: 5,
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Area chart with shadow
  ctx = document.getElementById("areaChartWithShadow");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithShadow",
      data: {
        labels: ["January", "February", "March", "April", "May", "June"],
        datasets: [
          {
            data: [5, 10, 15, 10, 15, 10],
            backgroundColor: "rgba(" + colors.primary + ", .1)",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            fill: true,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverBorderWidth: 2,
            pointHoverRadius: 6,
            tension: 0.5,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            display: false,
          },
          tooltip: tooltipOptions,
        },
        scales: {
          y: {
            grid: {
              display: true,
              drawBorder: false,
            },
            min: 0,
            max: 20,
            ticks: {
              stepSize: 5,
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Bar chart
  ctx = document.getElementById("barChart");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["January", "February", "March", "April", "May", "June"],
        datasets: [
          {
            label: "Potatoes",
            data: [5, 10, 15, 10, 15, 10],
            backgroundColor: "rgba(" + colors.primary + ", .1)",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
          {
            label: "Tomatoes",
            data: [7.5, 10, 17.5, 15, 12.5, 5],
            backgroundColor: "rgba(" + colors.primary + ", .5)",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
        scales: {
          y: {
            grid: {
              display: true,
              drawBorder: false,
            },
            min: 0,
            max: 20,
            ticks: {
              stepSize: 5,
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Bar chart with shadow
  ctx = document.getElementById("barChartWithShadow");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "barWithShadow",
      data: {
        labels: ["January", "February", "March", "April", "May", "June"],
        datasets: [
          {
            label: "Potatoes",
            data: [5, 10, 15, 10, 15, 10],
            backgroundColor: "rgba(" + colors.primary + ", .1)",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
          {
            label: "Tomatoes",
            data: [7.5, 10, 17.5, 15, 12.5, 5],
            backgroundColor: "rgba(" + colors.primary + ", .5)",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
        scales: {
          y: {
            grid: {
              display: true,
              drawBorder: false,
            },
            min: 0,
            max: 20,
            ticks: {
              stepSize: 5,
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Line chart
  ctx = document.getElementById("lineChart");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "line",
      data: {
        labels: ["January", "February", "March", "April", "May", "June"],
        datasets: [
          {
            data: [5, 10, 15, 10, 15, 10],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 8,
            pointHoverBorderWidth: 2,
            tension: 0.5,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            display: false,
          },
          tooltip: tooltipOptions,
        },
        scales: {
          y: {
            grid: {
              display: true,
              drawBorder: false,
            },
            min: 0,
            max: 20,
            ticks: {
              stepSize: 5,
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Line chart with shadow
  ctx = document.getElementById("lineChartWithShadow");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithShadow",
      data: {
        labels: ["January", "February", "March", "April", "May", "June"],
        datasets: [
          {
            data: [5, 10, 15, 10, 15, 10],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 8,
            pointHoverBorderWidth: 2,
            tension: 0.5,
          },
        ],
      },
      options: {
        plugins: {
          legend: {
            display: false,
          },
          tooltip: tooltipOptions,
        },
        scales: {
          y: {
            grid: {
              display: true,
              drawBorder: false,
            },
            min: 0,
            max: 20,
            ticks: {
              stepSize: 5,
            },
          },
          x: {
            grid: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Pie chart
  ctx = document.getElementById("pieChart");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "pie",
      data: {
        labels: ["Potatoes", "Tomatoes", "Onions"],
        datasets: [
          {
            data: [25, 10, 15],
            backgroundColor: [
              "rgba(" + colors.primary + ", .1)",
              "rgba(" + colors.primary + ", .5)",
              "rgba(" + colors.primary + ", .25)",
            ],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
      },
    });
  }

  // Pie chart with shadow
  ctx = document.getElementById("pieChartWithShadow");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "pieWithShadow",
      data: {
        labels: ["Potatoes", "Tomatoes", "Onions"],
        datasets: [
          {
            data: [25, 10, 15],
            backgroundColor: [
              "rgba(" + colors.primary + ", .1)",
              "rgba(" + colors.primary + ", .5)",
              "rgba(" + colors.primary + ", .25)",
            ],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
      },
    });
  }

  // Doughnut chart
  ctx = document.getElementById("doughnutChart");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Potatoes", "Tomatoes", "Onions"],
        datasets: [
          {
            data: [25, 10, 15],
            backgroundColor: [
              "rgba(" + colors.primary + ", .1)",
              "rgba(" + colors.primary + ", .5)",
              "rgba(" + colors.primary + ", .25)",
            ],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        cutout: "75%",
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
      },
    });
  }

  // Doughnut chart with shadow
  ctx = document.getElementById("doughnutChartWithShadow");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "doughnutWithShadow",
      data: {
        labels: ["Potatoes", "Tomatoes", "Onions"],
        datasets: [
          {
            data: [25, 10, 15],
            backgroundColor: [
              "rgba(" + colors.primary + ", .1)",
              "rgba(" + colors.primary + ", .5)",
              "rgba(" + colors.primary + ", .25)",
            ],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        cutout: "75%",
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
      },
    });
  }

  // Radar chart
  ctx = document.getElementById("radarChart");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "radar",
      data: {
        labels: ["Drinks", "Snacks", "Lunch", "Dinner"],
        datasets: [
          {
            label: "Potatoes",
            data: [25, 25, 25, 25],
            backgroundColor: "rgba(" + colors.primary + ", .1)",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            fill: true,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverBorderWidth: 2,
            pointHoverRadius: 6,
          },
          {
            label: "Tomatoes",
            data: [15, 15, 0, 15],
            backgroundColor: "rgba(" + colors.primary + ", .25",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            fill: true,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverBorderWidth: 2,
            pointHoverRadius: 6,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
        scales: {
          r: {
            max: 30,
            ticks: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Radar chart with shadow
  ctx = document.getElementById("radarChartWithShadow");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "radarWithShadow",
      data: {
        labels: ["Drinks", "Snacks", "Lunch", "Dinner"],
        datasets: [
          {
            label: "Potatoes",
            data: [25, 25, 25, 25],
            backgroundColor: "rgba(" + colors.primary + ", .1)",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            fill: true,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverBorderWidth: 2,
            pointHoverRadius: 6,
          },
          {
            label: "Tomatoes",
            data: [15, 15, 0, 15],
            backgroundColor: "rgba(" + colors.primary + ", .25",
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            fill: true,
            pointBackgroundColor: "#ffffff",
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverBorderWidth: 2,
            pointHoverRadius: 6,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
        scales: {
          r: {
            max: 30,
            ticks: {
              display: false,
            },
          },
        },
      },
    });
  }

  // Polar chart
  ctx = document.getElementById("polarChart");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "polarArea",
      data: {
        labels: ["Potatoes", "Tomatoes", "Onions"],
        datasets: [
          {
            data: [25, 10, 15],
            backgroundColor: [
              "rgba(" + colors.primary + ", .1)",
              "rgba(" + colors.primary + ", .5)",
              "rgba(" + colors.primary + ", .25)",
            ],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
        scales: {
          r: {
            ticks: {
              display: false,
            },
          },
        },
        layout: {
          padding: 5,
        },
      },
    });
  }

  // Polar chart with shadow
  ctx = document.getElementById("polarChartWithShadow");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "polarAreaWithShadow",
      data: {
        labels: ["Potatoes", "Tomatoes", "Onions"],
        datasets: [
          {
            data: [25, 10, 15],
            backgroundColor: [
              "rgba(" + colors.primary + ", .1)",
              "rgba(" + colors.primary + ", .5)",
              "rgba(" + colors.primary + ", .25)",
            ],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
            },
          },
          tooltip: tooltipOptions,
        },
        scales: {
          r: {
            ticks: {
              display: false,
            },
          },
        },
        layout: {
          padding: 5,
        },
      },
    });
  }

  // Line with annotation plugin
  const lineWithAnnotationPlugin = {
    afterInit: (chart) => {
      const info = chart.canvas.parentNode;

      const value = chart.data.datasets[0].data[0];
      const heading = chart.data.datasets[0].label;
      const label = chart.data.labels[0];

      info.querySelector(".chart-heading").innerHTML = heading;
      info.querySelector(".chart-value").innerHTML = "$" + value;
      info.querySelector(".chart-label").innerHTML = label;
    },
  };

  // Line with annotation options
  const lineWithAnnotationOptions = {
    plugins: {
      legend: {
        display: false,
      },
      tooltip: {
        enabled: false,
        intersect: false,
        external: (ctx) => {
          const info = ctx.chart.canvas.parentNode;

          const value = ctx.tooltip.dataPoints[0].formattedValue;
          const heading = ctx.tooltip.dataPoints[0].dataset.label;
          const label = ctx.tooltip.dataPoints[0].label;

          info.querySelector(".chart-heading").innerHTML = heading;
          info.querySelector(".chart-value").innerHTML = "$" + value;
          info.querySelector(".chart-label").innerHTML = label;
        },
      },
    },
    scales: {
      y: {
        display: false,
      },

      x: {
        display: false,
      },
    },
    layout: {
      padding: {
        left: 5,
        right: 5,
        top: 10,
        bottom: 10,
      },
    },
  };

  // Line with annotation chart 1
  ctx = document.getElementById("lineWithAnnotationChart1");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithAnnotation",
      plugins: [lineWithAnnotationPlugin],
      data: {
        labels: [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday",
        ],
        datasets: [
          {
            label: "Total Orders",
            data: [1250, 1300, 1550, 900, 1800, 1100, 1600],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 4,
            pointRadius: 2,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 2,
            tension: 0.5,
          },
        ],
      },
      options: lineWithAnnotationOptions,
    });
  }

  // Line with annotation chart 2
  ctx = document.getElementById("lineWithAnnotationChart2");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithAnnotation",
      plugins: [lineWithAnnotationPlugin],
      data: {
        labels: [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday",
        ],
        datasets: [
          {
            label: "Active Orders",
            data: [100, 125, 75, 125, 100, 75, 75],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 4,
            pointRadius: 2,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 2,
            tension: 0.5,
          },
        ],
      },
      options: lineWithAnnotationOptions,
    });
  }

  // Line with annotation chart 3
  ctx = document.getElementById("lineWithAnnotationChart3");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithAnnotation",
      plugins: [lineWithAnnotationPlugin],
      data: {
        labels: [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday",
        ],
        datasets: [
          {
            label: "Pending Orders",
            data: [300, 300, 600, 700, 600, 300, 300],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 4,
            pointRadius: 2,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 2,
            tension: 0.5,
          },
        ],
      },
      options: lineWithAnnotationOptions,
    });
  }

  // Line with annotation chart 4
  ctx = document.getElementById("lineWithAnnotationChart4");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithAnnotation",
      plugins: [lineWithAnnotationPlugin],
      data: {
        labels: [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday",
        ],
        datasets: [
          {
            label: "Shipped Orders",
            data: [200, 400, 200, 500, 100, 100, 400],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 4,
            pointRadius: 2,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 2,
            tension: 0.5,
          },
        ],
      },
      options: lineWithAnnotationOptions,
    });
  }

  // Line with annotation and shadow chart 1
  ctx = document.getElementById("lineWithAnnotationAndShadowChart1");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithAnnotationAndShadow",
      plugins: [lineWithAnnotationPlugin],
      data: {
        labels: [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday",
        ],
        datasets: [
          {
            label: "Total Orders",
            data: [1250, 1300, 1550, 900, 1800, 1100, 1600],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 4,
            pointRadius: 2,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 2,
            tension: 0.5,
          },
        ],
      },
      options: lineWithAnnotationOptions,
    });
  }

  // Line with annotation and shadow chart 2
  ctx = document.getElementById("lineWithAnnotationAndShadowChart2");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithAnnotationAndShadow",
      plugins: [lineWithAnnotationPlugin],
      data: {
        labels: [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday",
        ],
        datasets: [
          {
            label: "Active Orders",
            data: [100, 125, 75, 125, 100, 75, 75],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 4,
            pointRadius: 2,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 2,
            tension: 0.5,
          },
        ],
      },
      options: lineWithAnnotationOptions,
    });
  }

  // Line with annotation and shadow chart 3
  ctx = document.getElementById("lineWithAnnotationAndShadowChart3");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithAnnotationAndShadow",
      plugins: [lineWithAnnotationPlugin],
      data: {
        labels: [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday",
        ],
        datasets: [
          {
            label: "Pending Orders",
            data: [300, 300, 600, 700, 600, 300, 300],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 4,
            pointRadius: 2,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 2,
            tension: 0.5,
          },
        ],
      },
      options: lineWithAnnotationOptions,
    });
  }

  // Line with annotation and shadow chart 4
  ctx = document.getElementById("lineWithAnnotationAndShadowChart4");
  if (ctx) {
    ctx.getContext("2d");
    new Chart(ctx, {
      type: "lineWithAnnotationAndShadow",
      plugins: [lineWithAnnotationPlugin],
      data: {
        labels: [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday",
        ],
        datasets: [
          {
            label: "Shipped Orders",
            data: [200, 400, 200, 500, 100, 100, 400],
            borderColor: "rgba(" + colors.primary + ")",
            borderWidth: 2,
            pointBorderColor: "rgba(" + colors.primary + ")",
            pointBorderWidth: 4,
            pointRadius: 2,
            pointHoverBackgroundColor: "rgba(" + colors.primary + ")",
            pointHoverBorderColor: "#ffffff",
            pointHoverRadius: 2,
            tension: 0.5,
          },
        ],
      },
      options: lineWithAnnotationOptions,
    });
  }
}

// Custom File Input
const customFileInput = () => {
  on("body", "change", 'input[type="file"]', (event) => {
    const filename = event.target.value.split("\\").pop();
    event.target.parentNode.querySelector(".file-name").innerHTML = filename;
  });
};

customFileInput();

// Fullscreen
const fullscreen = () => {
  const fullScreenToggler = document.getElementById("fullScreenToggler");

  if (!fullScreenToggler) return;

  const element = document.documentElement;

  // Open fullscreen
  const openFullscreen = () => {
    if (element.requestFullscreen) {
      element.requestFullscreen();
    } else if (element.mozRequestFullScreen) {
      element.mozRequestFullScreen();
    } else if (element.webkitRequestFullscreen) {
      element.webkitRequestFullscreen();
    } else if (element.msRequestFullscreen) {
      element.msRequestFullscreen();
    }
  };

  // Close fullscreen
  const closeFullscreen = () => {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    }
  };

  // Check fullscreen
  const checkFullscreen = () => {
    if (
      document.fullscreenElement ||
      document.webkitFullscreenElement ||
      document.mozFullScreenElement ||
      document.msFullscreenElement
    ) {
      return true;
    }

    return false;
  };

  // Toggle Button Icon
  const togglerBtnIcon = () => {
    if (fullScreenToggler.classList.contains("la-expand-arrows-alt")) {
      fullScreenToggler.classList.remove("la-expand-arrows-alt");
      fullScreenToggler.classList.add("la-compress-arrows-alt");
    } else {
      fullScreenToggler.classList.remove("la-compress-arrows-alt");
      fullScreenToggler.classList.add("la-expand-arrows-alt");
    }
  };

  fullScreenToggler.addEventListener("click", () => {
    if (checkFullscreen()) {
      closeFullscreen();
    } else {
      openFullscreen();
    }

    togglerBtnIcon();
  });
};

fullscreen();

// Menu
const menu = () => {
  const root = document.documentElement;

  const menuType = localStorage.getItem("menuType");

  const menuBar = document.querySelector(".menu-bar");
  const menuItems = document.querySelector(".menu-items");

  if (!menuBar) return;

  if (menuType) {
    root.classList.add(menuType);
    menuBar.classList.add(menuType);
  }

  // Hide Menu Detail
  const hideMenuDetail = () => {
    menuBar.querySelectorAll(".menu-detail.open").forEach((menuDetail) => {
      hideOverlay();

      if (!menuBar.classList.contains("menu-wide")) {
        menuDetail.classList.remove("open");
      }
    });
  };

  // Hide Menu - When Clicked Elsewhere
  document.addEventListener("click", (event) => {
    if (
      !event.target.closest(".menu-items a") &&
      !event.target.closest(".menu-detail") &&
      !menuBar.classList.contains("menu-wide")
    ) {
      hideMenuDetail();
    }
  });

  // Menu Links
  on(".menu-items", "click", ".link", (event) => {
    const menuLink = event.target.closest(".link");
    const menu = menuLink.dataset.target;
    const selectedMenu = menuBar.querySelector(menu);

    if (!menuBar.classList.contains("menu-wide")) {
      if (selectedMenu) {
        showOverlay(true);
        selectedMenu.classList.add("open");
      } else {
        hideOverlay();
      }

      hideMenuDetail();

      if (selectedMenu) {
        showOverlay(true);
        selectedMenu.classList.add("open");
      } else {
        hideOverlay();
      }
    }
  });

  // Toggle Menu
  const toggleMenu = () => {
    if (menuBar.classList.contains("menu-hidden")) {
      root.classList.remove("menu-hidden");
      menuBar.classList.remove("menu-hidden");
    } else {
      root.classList.add("menu-hidden");
      menuBar.classList.add("menu-hidden");
    }
  };

  on(".top-bar", "click", "[data-toggle='menu']", (event) => {
    toggleMenu(event);
  });

  // Switch Menu Type
  const switchMenuType = (type) => {
    const openMenu = menuBar.querySelector(".menu-detail.open");

    switch (type) {
      case "icon-only":
        root.classList.add("menu-icon-only");
        menuBar.classList.add("menu-icon-only");
        localStorage.setItem("menuType", "menu-icon-only");

        if (menuBar.classList.contains("menu-wide")) {
          deactivateWide();

          if (openMenu) {
            showOverlay(true);
          }
        }

        break;
      case "wide":
        root.classList.add("menu-wide");
        menuBar.classList.add("menu-wide");
        localStorage.setItem("menuType", "menu-wide");

        root.classList.remove("menu-icon-only");
        menuBar.classList.remove("menu-icon-only");

        activateWide();

        if (openMenu) {
          hideOverlay();
        }

        break;
      case "hidden":
        root.classList.add("menu-hidden");
        menuBar.classList.add("menu-hidden");
        localStorage.setItem("menuType", "menu-hidden");

        hideMenuDetail();

        break;
      default:
        root.classList.remove("menu-icon-only");
        menuBar.classList.remove("menu-icon-only");
        localStorage.removeItem("menuType");

        if (menuBar.classList.contains("menu-wide")) {
          deactivateWide();

          if (openMenu) {
            showOverlay(true);
          }
        }
    }
  };

  // Activate Wide
  const activateWide = () => {
    menuBar.querySelector(".menu-header").classList.remove("hidden");

    menuBar.querySelectorAll(".menu-items .link").forEach((menuLink) => {
      const target = menuLink.dataset.target;

      const selectedMenu = menuBar.querySelector(".menu-detail" + target);
      if (selectedMenu) {
        selectedMenu.classList.add("collapse");
        menuLink.setAttribute("data-toggle", "collapse");
        menuLink.after(selectedMenu);
      }
    });
  };

  // Deactivate Wide
  const deactivateWide = () => {
    root.classList.remove("menu-wide");
    menuBar.classList.remove("menu-wide");

    menuBar.querySelector(".menu-header").classList.add("hidden");

    menuBar.querySelectorAll(".menu-items .link").forEach((menuLink) => {
      const target = menuLink.dataset.target;

      const selectedMenu = menuBar.querySelector(".menu-detail" + target);
      if (selectedMenu) {
        selectedMenu.classList.remove("collapse");
        menuLink.removeAttribute("data-toggle", "collapse");
        menuItems.after(selectedMenu);
      }
    });
  };

  // Auto-activate Wide
  if (menuBar.classList.contains("menu-wide")) {
    activateWide();
  }

  on(".menu-bar", "click", "[data-toggle='menu-type']", (event) => {
    const type = event.target.closest("[data-toggle='menu-type']").dataset
      .value;
    switchMenuType(type);
  });
};

menu();

// Show Active Page
const showActivePage = () => {
  const pageUrl = window.location.href.split(/[?#]/)[0];

  const pageLinkSelector = ".menu-bar a";

  const pageLinks = document.querySelectorAll(pageLinkSelector);

  if (!pageLinks) return;

  pageLinks.forEach((pageLink) => {
    if (pageLink.href === pageUrl) {
      pageLink.classList.add("active");

      const mainMenuTrigger = pageLink.closest(".menu-detail");

      if (!mainMenuTrigger) return;

      const mainMenu = document.querySelector(
        '.menu-items .link[data-target="[data-menu=' +
          mainMenuTrigger.dataset.menu +
          ']"]'
      );

      mainMenu.classList.add("active");
    }
  });
};

showActivePage();

// Modal
const modal = () => {
  // Show
  const showModal = (modal) => {
    showOverlay();
    modal.classList.add("active");
    const animation = modal.dataset.animations.split(", ")[0];
    const modalContent = modal.querySelector(".modal-content");
    animateCSS(modalContent, animation);

    modal.addEventListener("click", (event) => {
      if (modal.dataset.staticBackdrop !== undefined) return;
      if (modal !== event.target) return;
      closeModal(modal);
    });
  };

  on("body", "click", '[data-toggle="modal"]', (event) => {
    const modal = document.querySelector(event.target.dataset.target);
    showModal(modal);
  });

  // Close
  const closeModal = (modal) => {
    hideOverlay();
    const animation = modal.dataset.animations.split(", ")[1];
    const modalContent = modal.querySelector(".modal-content");
    animateCSS(modalContent, animation).then(() => {
      modal.classList.remove("active");
    });
  };

  on(".modal", "click", '[data-dismiss="modal"]', (event) => {
    const modal = event.target.closest(".modal");
    closeModal(modal);
  });
};

modal();

// Overlay
// Show
const showOverlay = (workspace) => {
  if (document.querySelector(".overlay")) return;

  document.body.classList.add("overlay-show");

  const overlay = document.createElement("div");
  if (workspace) {
    overlay.setAttribute("class", "overlay workspace");
  } else {
    overlay.setAttribute("class", "overlay");
  }

  document.body.appendChild(overlay);
  overlay.classList.add("active");
};

// Hide
const hideOverlay = () => {
  overlayToRemove = document.querySelector(".overlay");

  if (!overlayToRemove) return;

  document.body.classList.remove("overlay-show");

  overlayToRemove.classList.remove("active");
  document.body.removeChild(overlayToRemove);
};

// Rating Stars
const ratingStars = () => {
  rateStars = (event) => {
    const starsContainer = event.target.closest(".rating-stars");
    const stars = Array.from(starsContainer.children);
    const totalStars = stars.length;
    const index = stars.indexOf(event.target);
    let count = 0;
    count = totalStars - index;
    stars.forEach((star) => star.classList.remove("active"));

    event.target.classList.add("active");

    console.log("You have rated " + count + " stars.");
  };

  on("body", "click", ".rating-stars", (event) => {
    rateStars(event);
  });
};

ratingStars();

// Show Password
const showPassword = () => {
  // Toggle Show Password
  const toggleShowPassword = (showPasswordBtn) => {
    const password = showPasswordBtn
      .closest(".form-control-addon-within")
      .querySelector("input");

    if (password.type === "password") {
      password.type = "text";
      showPasswordBtn.classList.remove("text-gray-600", "dark:text-gray-600");
      showPasswordBtn.classList.add("text-primary", "dark:text-primary");
    } else {
      password.type = "password";
      showPasswordBtn.classList.remove("text-primary", "dark:text-primary");
      showPasswordBtn.classList.add("text-gray-600", "dark:text-gray-600");
    }
  };

  on("body", "click", '[data-toggle="password-visibility"]', (event) => {
    const showPasswordBtn = event.target.closest(
      '[data-toggle="password-visibility"]'
    );
    toggleShowPassword(showPasswordBtn);
  });
};

showPassword();

// Sidebar
const sidebar = () => {
  // Toggle Sidebar
  const toggleSidebar = () => {
    const sidebar = document.querySelector(".sidebar");
    if (sidebar.classList.contains("open")) {
      sidebar.classList.remove("open");
      hideOverlay();
    } else {
      sidebar.classList.add("open");
      showOverlay(true);
    }
  };

  on("body", "click", '[data-toggle="sidebar"]', () => {
    toggleSidebar();
  });
};

sidebar();

// Tabs
const tabs = () => {
  let toggling = false;

  on("body", "click", '[data-toggle="tab"]', (event) => {
    const trigger = event.target.closest('[data-toggle="tab"]');

    const tabs = trigger.closest(".tabs");
    const activeTabTrigger = tabs.querySelector(".tab-nav .active");
    const activeTab = tabs.querySelector(".collapse.open");
    const targetedTab = tabs.querySelector(trigger.dataset.target);

    if (toggling) return;
    if (activeTabTrigger === trigger) return;

    // Trigger
    activeTabTrigger.classList.remove("active");
    trigger.classList.add("active");

    // Tab
    // Close
    toggling = true;

    closeCollapse(activeTab, () => {
      openCollapse(targetedTab, () => {
        toggling = false;
      });
    });
  });

  // Wizard (Previous/Next)
  on("body", "click", '[data-toggle="wizard"]', (event) => {
    const wizard = event.target.closest(".wizard");
    const direction = event.target.dataset.direction;
    const tabLinks = wizard.querySelectorAll(".nav-link");
    const activeLink = wizard.querySelector(".nav-link.active");

    let activeIndex = 0;

    tabLinks.forEach((link, index) => {
      if (link === activeLink) {
        activeIndex = index;
      }
    });

    switch (direction) {
      case "next":
        if (tabLinks[activeIndex + 1]) {
          tabLinks[activeIndex + 1].click();
        }
        break;
      case "previous":
        if (tabLinks[activeIndex - 1]) {
          tabLinks[activeIndex - 1].click();
        }
        break;
    }
  });
};

tabs();

// Tippy
const customTippy = () => {
  // Menu tooltip
  tippy.delegate("body", {
    target: '.menu-icon-only [data-toggle="tooltip-menu"]',
    touch: ["hold", 500],
    theme: "light-border tooltip",
    offset: [0, 12],
    interactive: true,
    animation: "scale",
    placement: "right",
    appendTo: () => document.body,
  });

  // General tooltip
  tippy('[data-toggle="tooltip"]', {
    theme: "light-border tooltip",
    touch: ["hold", 500],
    offset: [0, 12],
    interactive: true,
    animation: "scale",
  });

  // Popover
  tippy('[data-toggle="popover"]', {
    theme: "light-border popover",
    offset: [0, 12],
    interactive: true,
    allowHTML: true,
    trigger: "click",
    animation: "shift-toward-extreme",
    content: (reference) => {
      const title = reference.dataset.popoverTitle;
      const content = reference.dataset.popoverContent;
      const popover =
        "<h5>" + title + "</h5>" + '<div class="mt-5">' + content + "</div>";
      return popover;
    },
  });

  // Dropdown
  tippy('[data-toggle="dropdown-menu"]', {
    theme: "light-border",
    zIndex: 25,
    offset: [0, 8],
    arrow: false,
    placement: "bottom-start",
    interactive: true,
    allowHTML: true,
    animation: "shift-toward-extreme",
    content: (reference) => {
      let dropdownMenu = reference
        .closest(".dropdown")
        .querySelector(".dropdown-menu");
      dropdownMenu = dropdownMenu.outerHTML;
      return dropdownMenu;
    },
  });

  // Custom Dropdown
  tippy('[data-toggle="custom-dropdown-menu"]', {
    theme: "light-border",
    zIndex: 25,
    offset: [0, 8],
    arrow: false,
    placement: "bottom-start",
    interactive: true,
    allowHTML: true,
    animation: "shift-toward-extreme",
    content: (reference) => {
      let dropdownMenu = reference
        .closest(".dropdown")
        .querySelector(".custom-dropdown-menu");
      dropdownMenu = dropdownMenu.outerHTML;
      return dropdownMenu;
    },
  });

  // Search & Select
  tippy('[data-toggle="search-select"]', {
    theme: "light-border",
    offset: [0, 8],
    maxWidth: "none",
    arrow: false,
    placement: "bottom-start",
    trigger: "click",
    interactive: true,
    allowHTML: true,
    animation: "shift-toward-extreme",
    content: (reference) => {
      let dropdownMenu = reference
        .closest(".search-select")
        .querySelector(".search-select-menu");
      dropdownMenu = dropdownMenu.outerHTML;
      return dropdownMenu;
    },
    appendTo(reference) {
      return reference.closest(".search-select");
    },
  });
};

customTippy();

// Toasts
const toasts = () => {
  const toastsContainer = document.getElementById("toasts-container");

  const toastCloseSelector = '[data-dismiss="toast"]';

  // Toast
  const createToast = (toast) => {
    const title = toast.dataset.title;
    const content = toast.dataset.content;
    const time = toast.dataset.time;
    let newToast =
      '<div class="toast mb-5">' +
      '<div class="toast-header">' +
      "<h5>" +
      title +
      "</h5>" +
      "<small>" +
      time +
      "</small>" +
      '<button type="button" class="close" data-dismiss="toast">&times</button>' +
      "</div>" +
      '<div class="toast-body">' +
      content +
      "</div>" +
      "</div>";

    newToast = new DOMParser().parseFromString(newToast, "text/html").body
      .firstChild;

    toastsContainer.appendChild(newToast);
    animateCSS(newToast, "fadeInUp");
  };

  on("body", "click", '[data-toggle="toast"]', (event) => {
    const toast = event.target;
    createToast(toast);
  });

  // Close Toast
  const closeToast = (toast) => {
    toast.style.overflowY = "hidden";
    toast.style.height = toast.offsetHeight + "px";

    animateCSS(toast, "fadeOutUp").then(() => {
      toast.style.transitionProperty =
        "height, margin, padding, border, opacity";
      toast.style.transitionDuration = "200ms";
      toast.style.transitionTimingFunction = "linear";

      toast.style.opacity = 0;
      toast.style.height = 0;
      toast.style.marginTop = 0;
      toast.style.marginBottom = 0;
      toast.style.paddingTop = 0;
      toast.style.paddingBottom = 0;
      toast.style.border = 0;
    });

    toast.addEventListener(
      "transitionend",
      () => {
        toast.parentNode ? toast.parentNode.removeChild(toast) : false;
      },
      { once: true }
    );
  };

  on("body", "click", toastCloseSelector, (event) => {
    const toast = event.target.closest(".toast");
    closeToast(toast);
  });
};

toasts();

//# sourceMappingURL=script.js.map
