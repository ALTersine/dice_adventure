<?php

namespace App\Factory;

use App\Entity\InfoDuPrix;
use App\Entity\Prix;
use App\Repository\PrixRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Centralise la gestion des prix (entités Prix + InfoDuPrix) :
 * un CRUD complet et une méthode d'affichage prête pour les boucles Twig.
 *
 * Service autowiré : injectez simplement PrixFactory dans vos contrôleurs.
 */
class PrixFactory
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PrixRepository $prixRepository,
    ) {
    }

    /**
     * CREATE — crée un prix, ses infos associées, puis l'enregistre en base.
     *
     * @param string[] $infos Liste des lignes d'information à rattacher au prix
     */
    public function create(
        string $nom,
        string $prixAffiche,
        string $prixPar,
        bool $mettreEnAvant = false,
        ?string $formuleContact = null,
        array $infos = [],
    ): Prix {
        $prix = new Prix($nom, $prixAffiche, $prixPar);
        $prix->setMettreEnAvant($mettreEnAvant);
        $prix->setFormuleContact($formuleContact);

        foreach ($infos as $info) {
            $this->ajouterInfo($prix, $info);
        }

        $this->em->persist($prix);
        $this->em->flush();

        return $prix;
    }

    /**
     * READ — récupère un prix (et ses infos) par son identifiant.
     */
    public function read(int $id): ?Prix
    {
        return $this->prixRepository->find($id);
    }

    /**
     * READ — récupère tous les prix sous forme d'entités.
     *
     * @return Prix[]
     */
    public function readAll(): array
    {
        return $this->prixRepository->findAll();
    }

    /**
     * UPDATE — met à jour un prix. Tout paramètre laissé à null est ignoré.
     * Si $infos est fourni (même vide), il remplace l'intégralité des infos existantes.
     *
     * @param string[]|null $infos
     */
    public function update(
        Prix $prix,
        ?string $nom = null,
        ?string $prixAffiche = null,
        ?string $prixPar = null,
        ?bool $mettreEnAvant = null,
        ?string $formuleContact = null,
        ?array $infos = null,
    ): Prix {
        if (null !== $nom) {
            $prix->setNom($nom);
        }
        if (null !== $prixAffiche) {
            $prix->setPrixAffiche($prixAffiche);
        }
        if (null !== $prixPar) {
            $prix->setPrixPar($prixPar);
        }
        if (null !== $mettreEnAvant) {
            $prix->setMettreEnAvant($mettreEnAvant);
        }

        $prix->setFormuleContact($formuleContact);

        if (null !== $infos) {
            // Supprime les anciennes infos avant de recréer les nouvelles.
            foreach ($prix->getInfosDuPrix()->toArray() as $ancienneInfo) {
                $prix->removeInfosDuPrix($ancienneInfo);
                $this->em->remove($ancienneInfo);
            }
            foreach ($infos as $info) {
                $this->ajouterInfo($prix, $info);
            }
        }

        $this->em->flush();

        return $prix;
    }

    /**
     * DELETE — supprime un prix ainsi que toutes ses infos associées
     * (pas de cascade configurée côté Doctrine, on nettoie donc manuellement).
     */
    public function delete(Prix $prix): void
    {
        foreach ($prix->getInfosDuPrix()->toArray() as $info) {
            $this->em->remove($info);
        }

        $this->em->remove($prix);
        $this->em->flush();
    }

    /**
     * Rattache une nouvelle info à un prix (gère les deux côtés de la relation
     * et marque l'entité pour persistance). Le flush reste à la charge de l'appelant.
     */
    public function ajouterInfo(Prix $prix, string $info): InfoDuPrix
    {
        $infoDuPrix = new InfoDuPrix($info, $prix);
        $prix->addInfosDuPrix($infoDuPrix);
        $this->em->persist($infoDuPrix);

        return $infoDuPrix;
    }

    /**
     * DISPLAY — renvoie tous les prix de la base sous forme de tableau,
     * avec des clés de référence explicites pour les boucles Twig.
     *
     * Exemple d'utilisation côté template :
     *
     *   {% for prix in prixList %}
     *     <h3>{{ prix.nom }}</h3>
     *     <p>{{ prix.prixAffiche }} <span>/ {{ prix.prixPar }}</span></p>
     *     {% if prix.mettreEnAvant %}<span class="badge">Le plus choisi</span>{% endif %}
     *     <ul>
     *       {% for info in prix.infos %}<li>{{ info }}</li>{% endfor %}
     *     </ul>
     *   {% endfor %}
     *
     * @return array<int, array{
     *     id: int|null,
     *     nom: string|null,
     *     prixAffiche: string|null,
     *     prixPar: string|null,
     *     mettreEnAvant: bool,
     *     infos: string[]
     * }>
     */
    public function display(): array
    {
        $prixList = [];

        foreach ($this->prixRepository->findAll() as $prix) {
            $infos = [];
            foreach ($prix->getInfosDuPrix() as $infoDuPrix) {
                $infos[] = $infoDuPrix->getInfo();
            }

            $prixList[] = [
                'id'            => $prix->getId(),
                'nom'           => $prix->getNom(),
                'prixAffiche'   => $prix->getPrixAffiche(),
                'prixPar'       => $prix->getPrixPar(),
                'mettreEnAvant' => $prix->isMettreEnAvant(),
                'formuleContact' => $prix->getFormuleContact() ?? 'Contacter nous',
                'infos'         => $infos,
            ];
        }

        return $prixList;
    }
}
