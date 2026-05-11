# InterviewPrep — Plateforme de Préparation aux Entretiens Techniques

> **Stack :** Laravel · PHP · MySQL · HTML5 · CSS3 · Groq API · Git  
> **Durée :** 5 jours · Lundi 11/05/2026 → Vendredi 15/05/2026 à 13h00  
> **Mode :** Individuel · Référentiel DWWM/Backend [2023]

---

## Contexte & Problème

Un développeur web marocain vient de terminer sa formation. Il décroche un entretien technique avec une startup SaaS à Casablanca — poste backend Laravel — dans **dix jours**.

**Le problème :** il sait beaucoup de choses, mais de façon éparpillée. Pas de structure claire, pas de visibilité sur ce qu'il maîtrise vraiment vs ce qu'il doit revoir.

**La solution :** InterviewPrep — une application Laravel pour organiser ses connaissances par domaine technique, rédiger ses notes, suivre son niveau de maîtrise, et générer des questions d'entretien réalistes via l'IA.

---

## User Stories

### 🔐 Authentification
| ID | Description |
|----|-------------|
| US1 | Inscription / Connexion / Déconnexion |

### 🗂️ Gestion des Domaines
| ID | Description |
|----|-------------|
| US2 | Voir la liste de mes domaines avec nb de concepts total et nb maîtrisés |
| US3 | Créer un domaine (nom + couleur de badge) |
| US4 | Modifier / Supprimer un domaine |

### ✍️ Gestion des Concepts
| ID | Description |
|----|-------------|
| US5 | Lister les concepts d'un domaine (titre, difficulté, statut) — filtrable par statut |
| US6 | Créer un concept (titre, explication, difficulté, statut initial = "à revoir") |
| US7 | Voir le détail d'un concept (titre, explication, niveau, statut, questions générées) |
| US8 | Modifier un concept (titre, explication, difficulté, statut) |
| US9 | Changer le statut rapidement depuis la liste (sans ouvrir le formulaire) |
| US10 | Supprimer un concept |

### 🤖 Génération AI de Questions d'Entretien
| ID | Description |
|----|-------------|
| US11 | Générer 5 questions d'entretien techniques depuis la page détail d'un concept via Groq API |
| US12 | Voir l'historique des générations (5 questions + date par génération) |
| US13 | Supprimer un lot de questions générées |

### ⭐ Bonus
- **Dashboard de progression** : stats par statut, domaine le mieux maîtrisé, domaine le plus à revoir
- **Soft deletes** sur les concepts : archiver au lieu de supprimer, page "Archivés" pour restaurer
- **Filtre combiné** : filtrer par statut ET niveau de difficulté simultanément

---

## Modèles de Données

### MCD (entités)
```
User ──< Domain ──< Concept ──< GeneratedQuestion
```

### MLD (tables)
| Table | Colonnes clés |
|-------|--------------|
| `users` | `id`, `name`, `email`, `password` |
| `domains` | `id`, `user_id` (FK), `name`, `color` |
| `concepts` | `id`, `domain_id` (FK), `title`, `explanation`, `difficulty` (junior/mid/senior), `status` (to_review/in_progress/mastered) |
| `generated_questions` | `id`, `concept_id` (FK), `questions` (JSON), `created_at` |

> ⚠️ MCD et MLD à valider **lundi avant tout code**.

---

## Architecture Laravel

### Relations Eloquent (3 niveaux)
```
User → hasMany → Domain
Domain → hasMany → Concept
Concept → hasMany → GeneratedQuestion
```

### Accessors requis
- `statusLabel()` → retourne "À revoir" / "En cours" / "Maîtrisé"
- `difficultyLabel()` → retourne "Junior" / "Mid" / "Senior"

### Validation
- Form Request classes pour **toutes** les validations (pas de `validate()` inline)

### Appel API AI
```php
// Dans un Service ou Controller — via Http:: facade natif Laravel
Http::withHeaders([
    'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
])->post('https://api.groq.com/openai/v1/chat/completions', [...]);
```
- Clé API dans `.env` uniquement — jamais dans le code, jamais commitée
- Résultat sauvegardé en base **avant** affichage
- Gestion d'erreur obligatoire (message propre si l'API ne répond pas)
- **Zéro N+1** — vérifié avec Laravel Debugbar

---

## Contraintes Techniques Obligatoires

### Workflow AI-Assisted
- `AGENTS.md` à la racine — **premier commit du Jour 1**
- Dossier `specs/` avec un fichier `.md` par feature construite avec un coding agent
- Messages de commits avec mention claire de l'usage AI
- Coding agent utilisé en **mode Plan → puis Build** pour chaque feature

### Outils & APIs recommandés
| Catégorie | Options |
|-----------|---------|
| **Coding agent** | OpenCode (recommandé), Claude Code, Gemini CLI, GitHub Copilot CLI |
| **API AI** | Groq API (recommandée — free tier, sans CB), Gemini API, Mistral API, Together AI |

---

## Branches Git
```
main
├── feature/domains-crud
├── feature/concepts-crud
└── feature/ai-generation
```
> Minimum **15 commits** avec messages explicites et mention usage AI. Commits quotidiens obligatoires.

---

## Livrables

| # | Livrable | Détails |
|---|---------|---------|
| 1 | **Repository GitHub** | 15+ commits, branches feature, dossier `specs/` public |
| 2 | **Jira Board** | Partagé avec `abderahmane.merradou@gmail.com` avant lundi 13h · toutes les US en tickets · historique de mouvement visible |
| 3 | **MCD & MLD** | Rendu lundi — pas de code avant validation |
| 4 | **Présentation** | Structure imposée (voir ci-dessous) · max 30 mots/slide · min 1 visuel/slide · police ≥ 24px · slides numérotés |
| 5 | **Dossier `specs/`** | 1 fichier `.md` par feature · commité dans le repo |
| 6 | **README.md** | Dans le repo |

### Structure de la présentation (ordre obligatoire)
1. Titre & Auteur
2. Contexte & Problème
3. MCD
4. MLD
5. Stack & Outils (Laravel + coding agent + API AI)
6. Workflow AI-Assisted *(screenshots réels des specs et commits)*
7. Feature AI *(comment l'appel `Http::` fonctionne)*
8. Démo live
9. Ce que l'agent a bien fait / Ce qu'il a mal fait ou hallucin é
10. Conclusion

---

## Critères d'Évaluation

| Critère | Poids | Points clés |
|---------|-------|-------------|
| **Architecture Laravel** | 30% | Relations Eloquent 3 niveaux · Form Requests · Accessors · Http:: facade · Zéro N+1 |
| **Fonctionnalités** | 25% | CRUD Domains complet · CRUD Concepts + changement statut rapide · Génération AI + historique supprimable |
| **Workflow AI-Assisted** | 25% | AGENTS.md · specs/ (≥3 fichiers) · commits AI · explication démo |
| **Présentation** | 20% | Structure respectée · Screenshots réels · Règles slides |

### Questions de démo attendues
- *"Montre un fichier dans `specs/`. Comment as-tu structuré la spec ? Qu'est-ce qui a changé après avoir précisé 'Ce que je NE veux PAS' ?"*
- *"Montre un commit avec mention AI. Qu'est-ce que l'agent a généré ? Qu'est-ce que tu as modifié manuellement et pourquoi ?"*

---

## Checklist Jour 1

- [ ] Créer le repo GitHub
- [ ] Commiter `AGENTS.md` en premier (commit #1)
- [ ] Créer le board Jira et le partager
- [ ] Rédiger et faire valider le MCD + MLD
- [ ] Initialiser le projet Laravel
- [ ] Créer les branches feature
