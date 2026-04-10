const offers = [
  { title: 'Eco-app de gestion de déchets', type: 'Mission IA', skills: ['javascript', 'node', 'data'], budget: '2 200 TND', description: 'Développer un algorithme de tri intelligent pour communes locales.' },
  { title: 'Marketplace réparation solidaire', type: 'Projet collaboratif', skills: ['php', 'laravel', 'ui/ux'], budget: '1 650 TND', description: 'Créer une plateforme d’échange de services entre voisins.' },
  { title: 'Bot de matching de compétences', type: 'Plateforme', skills: ['python', 'ai', 'ml'], budget: '3 800 TND', description: 'Système interne pour recommander partenaires en temps réel.' },
  { title: 'Workshop SEO + contenu', type: 'Échange', skills: ['marketing', 'seo', 'copywriting'], budget: '3 crédits', description: 'Session entre freelances pour gagner en visibilité et pratique.' }
];

function renderOffers(filtered) {
  const container = document.getElementById('offers');
  if (!container) return;
  container.innerHTML = '';

  if (!filtered.length) {
    container.innerHTML = '<div class="empty-state"><strong>Aucune opportunité trouvée.</strong><br>Essayez un autre mot-clé comme "javascript", "design" ou "marketing".</div>';
    return;
  }

  filtered.forEach((offer) => {
    const card = document.createElement('div');
    card.className = 'offer-card';
    card.innerHTML = `
      <h3>${offer.title}</h3>
      <p>${offer.description}</p>
      <p class="meta"><strong>Type :</strong> ${offer.type}</p>
      <p class="meta"><strong>Budget :</strong> ${offer.budget}</p>
      <div class="tag-list">
        ${offer.skills.map(skill => `<span class="tag">${skill}</span>`).join('')}
      </div>
    `;
    container.appendChild(card);
  });
}

function aiMatch(skillQuery) {
  if (!skillQuery) return offers;
  const lower = skillQuery.toLowerCase();
  return offers.filter((offer) =>
    offer.skills.some((s) => s.toLowerCase().includes(lower)) ||
    offer.title.toLowerCase().includes(lower) ||
    offer.type.toLowerCase().includes(lower) ||
    offer.description.toLowerCase().includes(lower)
  );
}

function handleOfferSearch() {
  const input = document.getElementById('skillInput');
  if (!input) return;
  const value = input.value.trim();
  renderOffers(aiMatch(value));
}

document.addEventListener('DOMContentLoaded', () => {
  renderOffers(offers);

  const findBtn = document.getElementById('findOffersBtn');
  const input = document.getElementById('skillInput');

  if (findBtn) {
    findBtn.addEventListener('click', handleOfferSearch);
  }

  if (input) {
    input.addEventListener('keyup', (event) => {
      if (event.key === 'Enter') {
        handleOfferSearch();
      }
    });
  }
});
