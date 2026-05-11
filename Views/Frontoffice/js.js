const offers = [
  { title: 'Eco-app de gestion de déchets', type: 'Mission IA', skills: ['javascript', 'node', 'data'], budget: '2 200 TND', description: 'Développer un algorithme de tri intelligent pour communes locales.' },
  { title: 'Marketplace réparation solidaire', type: 'Projet collaboratif', skills: ['php', 'laravel', 'ui/ux'], budget: '1 650 TND', description: 'Créer une plateforme d’échange de services entre voisins.' },
  { title: 'Bot de matching de compétences', type: 'Plateforme', skills: ['python', 'ai', 'ml'], budget: '3 800 TND', description: 'Système interne pour recommander partenaires en temps réel.' },
  { title: 'Workshop SEO + contenu', type: 'Échange', skills: ['marketing', 'seo', 'copywriting'], budget: '3 crédits', description: 'Session entre freelances pour gagner en visibilité et pratique.' }
];

function renderOffers(filtered) {
  const container = document.getElementById('offers');
  container.innerHTML = '';
  if (!filtered.length) {
    container.innerHTML = '<p>Aucune opportunité détectée, essayez une autre compétence.</p>';
    return;
  }
  filtered.forEach((offer) => {
    const card = document.createElement('div');
    card.className = 'card';
    card.innerHTML = `
      <h3>${offer.title}</h3>
      <p>${offer.description}</p>
      <p class="meta"><strong>Type :</strong> ${offer.type}</p>
      <p class="meta"><strong>Compétences :</strong> ${offer.skills.join(', ')}</p>
      <p class="meta"><strong>Budget :</strong> ${offer.budget}</p>
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
    offer.type.toLowerCase().includes(lower)
  );
}

document.getElementById('findOffersBtn').addEventListener('click', () => {
  const value = document.getElementById('skillInput').value.trim();
  renderOffers(aiMatch(value));
});

renderOffers(offers);
