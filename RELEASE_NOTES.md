# [DigiQuali] [23.0.0] - Type de question NF/ISO 9001 - Sauvegarde AJAX des contrôles

Description : Cette version introduit les types de questions Marque NF et ISO 9001 avec statistiques par tag, l'intégration des tâches de projet sur les contrôles, et une refonte de la sauvegarde des réponses en AJAX (auto-save sur clic et au flou des commentaires).

## Nouvelles fonctionnalités et innovations

### Types de questions Marque NF et ISO 9001

* Nouveau type de question **Marque NF** avec statistiques de conformité par tag.
* Nouveau type de question **ISO 9001** avec ses propres réponses (boutons aux couleurs pastel) et tooltips wpeo.
* Génération automatique des catégories de questions Marque NF à l'initialisation du module.
* Pourcentage de conformité affiché par tag sur la carte de contrôle ; les questions sans tag sont regroupées sous une catégorie virtuelle « Pas de tag ».

<!-- 📸 Ajouter une screenshot ici -->

### Sauvegarde AJAX des contrôles

* Auto-save en AJAX à chaque clic sur une réponse, plus besoin de cliquer sur « Enregistrer ».
* Auto-save des commentaires au flou (blur) du champ.
* Feedback visuel bref sur le bouton « Enregistrer » à la sauvegarde réussie.
* Mapping des `rowid` ControlLine restauré → fin des échecs silencieux de persistance AJAX.

<!-- 📸 Ajouter une screenshot ici -->

### Tâches de projet liées aux contrôles

* Nouveau champ `fk_project` sur la fiche modèle (Sheet).
* Création automatique d'une tâche de projet à la création d'un contrôle.
* Stockage de `fk_master_task` sur le contrôle pour pouvoir relier les tâches enfants.
* `fk_task_parent` positionné sur la tâche maître à la création de tâche par question.
* Cases à cocher des éléments liés (`element_linked`) restaurées avec le projet.

<!-- 📸 Ajouter une screenshot ici -->

### Sélection de masse / champs obligatoires

* Case à cocher « Sélectionner toutes les questions obligatoires » sur le modèle de fiche.
* Initialisation et bascule du `mandatory-all` gérées dans le JS minifié.

### Médias et badges

* Badge avec le nombre de médias affiché dans les actions DigiQuali.
* Affichage corrigé des items de la page média lorsqu'il n'y a pas de photo ni de label.

---

## Améliorations & corrections

### Carte de contrôle / sauvegarde

* Sauvegarde manuelle des réponses corrigée : warnings PHP et vérifications `empty` strictes éliminés.
* Détournement global de clic JS qui empêchait le POST manuel — corrigé.
* Avertissement bloquant `array_shift` sur la fiche de contrôle — corrigé.
* Égalité stricte revertée à égalité large pour empêcher le bypass de mise à jour DB.
* Erreur de syntaxe SQL dans `llx_digiquali_controldet.sql` corrigée.
* Boîte de commentaire toujours disponible après soumission, statut `disabled` correctement géré.
* Vérification de `fkSheet` avant manipulation de création de contrôle.
* Erreur BOM sur le contrôle corrigée.

### PDF des contrôles

* Génération de miniatures (`thumb`) corrigée — 2 passes successives.
* Style et code du PDF de contrôle corrigés.
* Nom de document corrigé.

### Compatibilité Saturne / SaturneObject

* Signature de `getTriggerDescription` alignée sur celle de SaturneObject.
* `getTriggerDescription` protégé contre les propriétés typées non initialisées via guard sur `fetch()`.
* Type de retour `void` ajouté à `initAsSpecimen` sur QuestionGroup.
* `next_control_date` retiré du select hook de la liste Survey (incompatible avec la liste générique).
* Cache `objectsMetadata` initialisé dans `printFieldListSearch`.

### Liste des questions / surveys

* Gestion des réponses vides dans le calcul du pourcentage.
* Comptage des questions par groupe sur la liste des fiches.
* Ordre du champ `days_remaining_before_next_control` corrigé.
* Préfixe de clé ajouté au champ `element_type` pour les labels de boutons.
* `commonfields_view` ajouté pour afficher la description sur DigiQualiElement.

### Intégration DoliCar

* Numéro d'immatriculation DoliCar affiché dans le sélecteur de lot produit.
* Sur le certificat d'immatriculation, ordre d'affichage des lots réorganisé en CG-VIN-type véhicule.

### Robustesse PHP

* `[Actions]` : `0` (int) passé à `shortener->fetch()` au lieu d'une chaîne.
* `[Core]` : `__DIR__` utilisé au lieu de `DOL_DOCUMENT_ROOT` pour le chemin Dolistore.
* Plusieurs passes de nettoyage de code sur les classes (`[Class] core: clean code`).
* `[JS] fix: window.digiquali.activity.updateContentEditable` — appel JS corrigé.

### SQL

* Ligne SQL manquante pour Survey ajoutée.
* Colonne inutile retirée de `questiondet`.

### Traductions

* Traductions ajoutées sur la liste `Controldet`.
* Trad « % Conformité » corrigée.

## Comparaison des versions [22.0.0](https://github.com/Evarisk/DigiQuali/compare/22.0.0...23.0.0) et 23.0.0

* [#2380] [Question] add: auto-generate nf question categories on module init [`9008b927`](https://github.com/Evarisk/DigiQuali/commit/9008b927)
* [#2369] [Question] add: iso9001 question type with wpeo tooltips on answers [`64693695`](https://github.com/Evarisk/DigiQuali/commit/64693695)
* [#2369] [Question|Control] add: MarqueNF question type and per-tag statistics [`9ad3a6b0`](https://github.com/Evarisk/DigiQuali/commit/9ad3a6b0)
* [#2383] [Survey] fix: guard fetch in getTriggerDescription against uninitialized typed property [`9cf225f1`](https://github.com/Evarisk/DigiQuali/commit/9cf225f1)
* [#2378] [DigiQualiElement] fix: add prefix key to element_type field for button labels [`3d37302e`](https://github.com/Evarisk/DigiQuali/commit/3d37302e)
* [#2374] [DigiQuali] fix: update getTriggerDescription signature to match SaturneObject [`aa5556d8`](https://github.com/Evarisk/DigiQuali/commit/aa5556d8)
* [#2370] [Sheet/Control] add: project task integration on control creation [`7c30dbbc`](https://github.com/Evarisk/DigiQuali/commit/7c30dbbc) [`671f6954`](https://github.com/Evarisk/DigiQuali/commit/671f6954) [`0781b6d9`](https://github.com/Evarisk/DigiQuali/commit/0781b6d9) [`6c1d2334`](https://github.com/Evarisk/DigiQuali/commit/6c1d2334)
* [#2365] [Sheet] fix: count questions in groups for sheet list [`1c0f9f2d`](https://github.com/Evarisk/DigiQuali/commit/1c0f9f2d)
* [#2356] [Control] fix: avertissement bloquant array_shift sur fiche de contrôle [`2275e30c`](https://github.com/Evarisk/DigiQuali/commit/2275e30c)
* [#2352] [Survey] fix: initialize objectsMetadata cache in printFieldListSearch [`62a17ab3`](https://github.com/Evarisk/DigiQuali/commit/62a17ab3)
* [#2351] [Control] add: display dolicar registration number in productlot selector [`0657491a`](https://github.com/Evarisk/DigiQuali/commit/0657491a)
* [#2347] [Actions] fix: pass int 0 instead of string to shortener fetch() [`11295a2b`](https://github.com/Evarisk/DigiQuali/commit/11295a2b)
* [#2346] [Sheet/Survey] add: select all mandatory questions checkbox [`eceda0e4`](https://github.com/Evarisk/DigiQuali/commit/eceda0e4) [`4bf3cf56`](https://github.com/Evarisk/DigiQuali/commit/4bf3cf56) [`d072afb4`](https://github.com/Evarisk/DigiQuali/commit/d072afb4)
* [#2344] [QuestionGroup/Survey] fix: void return type on initAsSpecimen, remove next_control_date from select hook [`29ef9dc4`](https://github.com/Evarisk/DigiQuali/commit/29ef9dc4) [`4c318056`](https://github.com/Evarisk/DigiQuali/commit/4c318056)
* [#2338] [ActionsDigiquali/ObjectMedia] add: badge media count, fix media page display [`a4a6f34c`](https://github.com/Evarisk/DigiQuali/commit/a4a6f34c) [`c232ad94`](https://github.com/Evarisk/DigiQuali/commit/c232ad94)
* [#2330] [List] fix: days_remaining_before_next_control field order [`2b3062d6`](https://github.com/Evarisk/DigiQuali/commit/2b3062d6)
* [#2327] [Control] fix: need to check fkSheet before control creation manipulation [`7f1a2125`](https://github.com/Evarisk/DigiQuali/commit/7f1a2125)
* [#2323] [Control] fix: comment box availability and disabled status [`a0723b79`](https://github.com/Evarisk/DigiQuali/commit/a0723b79) [`48352b79`](https://github.com/Evarisk/DigiQuali/commit/48352b79)
* [#2320] [JS] fix: window.digiquali.activity.updateContentEditable [`60f4ae8d`](https://github.com/Evarisk/DigiQuali/commit/60f4ae8d)
* [#2318] [SQL] fix/remove: missing line for survey, useless column in questiondet [`73a084e9`](https://github.com/Evarisk/DigiQuali/commit/73a084e9) [`7fcab62d`](https://github.com/Evarisk/DigiQuali/commit/7fcab62d)
* [#2304] [PDF] fix: wrong document name [`4bd94679`](https://github.com/Evarisk/DigiQuali/commit/4bd94679)
* [#2303] [ControldetList] add: translation [`eff1e553`](https://github.com/Evarisk/DigiQuali/commit/eff1e553)
* [#2285] [DigiQualiElement] fix: add commonfields_view to display description [`7c048859`](https://github.com/Evarisk/DigiQuali/commit/7c048859)
* [#2130] [PDF] fix: bug on thumb generation [`2a6cf4f0`](https://github.com/Evarisk/DigiQuali/commit/2a6cf4f0) [`0cb1ef75`](https://github.com/Evarisk/DigiQuali/commit/0cb1ef75)
* [#1902] [PDF] fix: control pdf in code and in style [`81efa117`](https://github.com/Evarisk/DigiQuali/commit/81efa117)
* [#373] [RegistrationCertificate] fix: reorder lot display to CG-VIN-vehicle type [`feca814c`](https://github.com/Evarisk/DigiQuali/commit/feca814c)
