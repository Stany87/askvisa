document.addEventListener('DOMContentLoaded', () => {

  // ── Hamburger ──
  const hamburger = document.getElementById('hamburgerBtn');
  const mobileMenu = document.getElementById('mobileMenu');
  const mmClose = document.getElementById('mmClose');
  hamburger?.addEventListener('click', () => mobileMenu.classList.add('open'));
  mmClose?.addEventListener('click', () => mobileMenu.classList.remove('open'));
  mobileMenu?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => mobileMenu.classList.remove('open')));

  // ── Smooth scroll ──
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      const t = document.querySelector(a.getAttribute('href'));
      if (t) t.scrollIntoView({ behavior:'smooth', block:'start' });
    });
  });

  // ── Visa data ──
  const countries = {
    thailand: {
      name: 'Thailand', flag: '🇹🇭',
      visas: [
        { type: 'Tourist Visa', processing: '2-3 Business Days', validity: '60 Days', docs: 'Passport, Photo, Flight Itinerary, and Hotel Booking', price: '₹80.00 INR' },
        { type: 'TDAC', processing: '24-72 Hours', validity: '30 Days', docs: 'Valid Passport, Passport Photo', price: '₹60.00 INR' },
        { type: 'Business Visa', processing: '5-7 Business Days', validity: '90 Days', docs: 'Passport, Invitation Letter, Company Docs', price: '₹250.00 INR' }
      ]
    },
    malaysia: {
      name: 'Malaysia', flag: '🇲🇾',
      visas: [
        { type: 'eVisa (Tourism)', processing: '24-48 Hours', validity: '30 Days Single Entry', docs: 'Passport, Photo, Return Tickets, Hotel Reservation', price: '₹120.00 INR' },
        { type: 'eNTRI', processing: 'Instant - 24 Hours', validity: '15 Days Single Entry', docs: 'Valid Passport, Return Tickets', price: '₹80.00 INR' }
      ]
    },
    hongkong: {
      name: 'Hong Kong', flag: '🇭🇰',
      visas: [
        { type: 'PAR (Pre-Arrival)', processing: '24-48 Hours', validity: '14-90 Days', docs: 'Passport, Photo, Confirmed Tickets, Hotel Booking', price: '₹150.00 INR' },
        { type: 'Tourist Visa', processing: '3-5 Business Days', validity: '14-90 Days', docs: 'Passport, Photo, Proof of Funds, Flight Itinerary', price: '₹200.00 INR' }
      ]
    },
    singapore: {
      name: 'Singapore', flag: '🇸🇬',
      visas: [
        { type: 'Tourist Visa', processing: '5-7 Days', validity: '30 Days', docs: 'Passport, Photo, Cover Letter, Bank Statement', price: '₹200.00 INR', soon: true }
      ]
    },
    vietnam: {
      name: 'Vietnam', flag: '🇻🇳',
      visas: [
        { type: 'e-Visa', processing: '3-5 Days', validity: '30 Days', docs: 'Passport, Photo, Flight Itinerary', price: '₹90.00 INR', soon: true }
      ]
    },
    dubai: {
      name: 'Dubai (UAE)', flag: '🇦🇪',
      visas: [
        { type: 'Tourist Visa', processing: '3-4 Days', validity: '30 Days', docs: 'Passport, Photo, Bank Statement, Hotel Booking', price: '₹180.00 INR', soon: true }
      ]
    },
    srilanka: {
      name: 'Sri Lanka', flag: '🇱🇰',
      visas: [
        { type: 'ETA', processing: '24-48 Hours', validity: '30 Days', docs: 'Passport, Photo, Return Tickets', price: '₹60.00 INR', soon: true }
      ]
    },
    indonesia: {
      name: 'Indonesia', flag: '🇮🇩',
      visas: [
        { type: 'e-VOA', processing: 'Instant - 48 Hours', validity: '30 Days', docs: 'Passport, Photo, Return Tickets', price: '₹70.00 INR', soon: true }
      ]
    },
    japan: {
      name: 'Japan', flag: '🇯🇵',
      visas: [
        { type: 'Tourist Visa', processing: '7-10 Days', validity: '15-90 Days', docs: 'Passport, Photo, ITR, Bank Statement', price: '₹350.00 INR', soon: true }
      ]
    }
  };

  const countrySelect = document.getElementById('countrySelect');
  const visaTypeSelect = document.getElementById('visaTypeSelect');
  const searchBtn = document.getElementById('searchBtn');
  const grid = document.getElementById('resultsGrid');

  // Populate visa type dropdown when country changes
  countrySelect.addEventListener('change', () => {
    const key = countrySelect.value;
    visaTypeSelect.innerHTML = '<option value="">All Visa Types</option>';
    if (key && countries[key]) {
      countries[key].visas.forEach((v, i) => {
        visaTypeSelect.innerHTML += `<option value="${i}">${v.type}</option>`;
      });
    }
  });

  // Render all cards on load
  renderCards();

  // Search button
  searchBtn.addEventListener('click', () => {
    renderCards(countrySelect.value, visaTypeSelect.value);
    document.getElementById('results')?.scrollIntoView({ behavior:'smooth', block:'start' });
  });

  function renderCards(filterCountry, filterVisa) {
    grid.innerHTML = '';
    for (const [key, country] of Object.entries(countries)) {
      if (filterCountry && filterCountry !== key) continue;
      country.visas.forEach((visa, i) => {
        if (filterVisa !== '' && filterVisa !== undefined && parseInt(filterVisa) !== i) return;
        // Only skip if filtering by country AND visa type for "coming soon"
        grid.innerHTML += buildCard(country, visa);
      });
    }
  }

  function buildCard(country, visa) {
    const isSoon = visa.soon;
    return `
      <div class="visa-card">
        <div class="vc-head">
          <span class="vc-flag">${country.flag}</span>
          <span class="vc-title">${country.name} - ${visa.type}</span>
        </div>
        <div class="vc-info">
          <p class="vc-row"><strong>Processing Time:</strong> ${visa.processing}</p>
          <p class="vc-row"><strong>Validity:</strong> ${visa.validity}</p>
          <p class="vc-row"><strong>Required Documents:</strong> ${visa.docs}</p>
        </div>
        <p class="vc-price-label">Agent Pricing</p>
        <p class="vc-price">${visa.price}</p>
        <div class="vc-btns">
          ${isSoon
            ? '<button class="btn-details" disabled style="opacity:.5;cursor:default">Coming Soon</button>'
            : `<button class="btn-apply" onclick="applyNow('${country.name}')">Apply Now</button><button class="btn-details" onclick="viewDetails('${country.name}','${visa.type}')">View Details</button>`
          }
        </div>
      </div>`;
  }

  // ── Actions ──
  window.applyNow = (name) => {
    window.open(`../index.php?country=${encodeURIComponent(name)}&source=b2b&auto_open=chat`, '_blank');
  };

  window.viewDetails = (name, type) => {
    alert(`${name} — ${type}\n\nFull details page coming soon.\nUse "Apply Now" to start processing.`);
  };
});
