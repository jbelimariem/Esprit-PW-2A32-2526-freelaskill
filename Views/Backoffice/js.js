const panels = {
  missions: `
    <section class="panel">
      <h2 class="section-title">Missions Solidaires</h2>
      <p>Activités éthiques et impact local validées par l’administrateur.</p>
      <table class="table">
        <tr><th>ID</th><th>Titre</th><th>Type</th><th>Statut</th></tr>
        <tr><td>F01</td><td>Plateforme d’éco-collecte</td><td>Collaboratif</td><td>En cours</td></tr>
        <tr><td>F02</td><td>Application e-learning</td><td>Échange de compétences</td><td>Publié</td></tr>
      </table>
    </section>
  `,
  colearning: `
    <section class="panel">
      <h2 class="section-title">Programmes Co-learning</h2>
      <p>Gérez les sessions en ligne et les crédits d’échange de compétences.</p>
      <ul>
        <li>Cycle Agile & DevOps (15 inscrits) - 5 crédits</li>
        <li>Atelier IA & Data (10 inscrits) - 4 crédits</li>
        <li>Mentorat freelance + pitch (8 inscrits) - 3 crédits</li>
      </ul>
    </section>
  `,
  community: `
    <section class="panel">
      <h2 class="section-title">Indices de Communauté</h2>
      <p>Suivi du taux de participation, du taux de satisfaction et des partenariats locaux.</p>
      <ul>
        <li>Nombre de freelances actifs : 220</li>
        <li>Projets co-construits : 34</li>
        <li>Taux de recommandation : 92%</li>
      </ul>
    </section>
  `
};

function setActive(panelKey) {
  document.querySelectorAll('.nav button').forEach((btn) => {
    btn.classList.toggle('active', btn.dataset.panel === panelKey);
  });
  document.getElementById('panels').innerHTML = panels[panelKey] || '';
}

document.querySelectorAll('.nav button').forEach((btn) => {
  btn.addEventListener('click', () => setActive(btn.dataset.panel));
});

setActive('missions');
