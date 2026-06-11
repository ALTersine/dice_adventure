<?php

namespace App\Repository;

use App\Entity\Avis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /**
     * Renvoie tous les avis sous forme de tableau, avec des clés explicites
     * prêtes à être bouclées dans un template Twig.
     *
     * Exemple côté template :
     *
     *   {% for avis in avisList %}
     *     <figure class="quote">
     *       <div class="stars">{% for i in 1..avis.note %}★{% endfor %}{% if avis.note < 5 %}{% for i in avis.note+1..5 %}☆{% endfor %}{% endif %}</div>
     *       <p>« {{ avis.message }} »</p>
     *       <figcaption>— {{ avis.auteur }}{% if avis.infosSuppAuteur %}, {{ avis.infosSuppAuteur }}{% endif %}</figcaption>
     *     </figure>
     *   {% endfor %}
     *
     * @return array<int, array{
     *     id: int|null,
     *     auteur: string|null,
     *     infosSuppAuteur: string|null,
     *     message: string|null,
     *     note: int|null
     * }>
     */
    public function findAllForDisplay(): array
    {
        $avisList = [];

        foreach ($this->findBy([], ['id' => 'DESC']) as $avis) {
            $avisList[] = [
                'id'              => $avis->getId(),
                'auteur'          => $avis->getAuteur(),
                'infosSuppAuteur' => $avis->getInfosSuppAuteur(),
                'message'         => $avis->getMessage(),
                'note'            => $avis->getNote(),
            ];
        }

        return $avisList;
    }

    /**
     * Crée un avis et l'enregistre en base.
     */
    public function create(
        string $auteur,
        string $message,
        int $note,
        ?string $infosSuppAuteur = null,
    ): Avis {
        $avis = new Avis();
        $avis->setAuteur($auteur);
        $avis->setMessage($message);
        $avis->setNote($note);
        $avis->setInfosSuppAuteur($infosSuppAuteur);

        $em = $this->getEntityManager();
        $em->persist($avis);
        $em->flush();

        return $avis;
    }

    /**
     * Supprime un avis de la base.
     */
    public function delete(Avis $avis): void
    {
        $em = $this->getEntityManager();
        $em->remove($avis);
        $em->flush();
    }
}
