<?php

use \ActivityStream\Client\Model\Resource;
use \ActivityStream\Client\Model\ResourceInterface;

class DAORecordKernel_bu_ecole_classe implements ResourceInterface
{
  protected $_levels = null;
  protected $_school = null;

  public function __toString ()
  {
    return $this->nom.' ('.implode(' - ', $this->getLevels ()->fetchAll ()).')';
  }

  /**
   * Return a resource from the current Object
   *
   * @return Resource
   */
  public function toResource()
  {
    $resource = new EcoleNumeriqueActivityStreamResource(
      $this->nom,
      get_class($this),
      $this->id
    );

    $attributes = array(
      'ecole',
      'annee_scol',
      'is_validee',
      'is_supprimee',
      '_levels',
      '_school',
    );

    $attributesValues = array();
    foreach ($attributes as $attribute) {
      $attributesValues[$attribute] = $this->$attribute;
    }

    $resource->setAttributes($attributesValues);

    return $resource;
  }

  public function getSchool ()
  {
    if (is_null($this->_school)) {

      $schoolDAO = _ioDAO ('kernel|kernel_bu_ecole');

      $this->_school = $schoolDAO->get ($this->ecole);
    }

    return $this->_school;
  }

  public function getLevels ()
  {
    if (is_null($this->_levels)) {

      $levelDAO = _ioDAO ('kernel|kernel_bu_classe_niveau');
      $this->_levels = $levelDAO->findByClassId ($this->id);
    }

    return $this->_levels;
  }
}

class DAOKernel_bu_ecole_classe
{
    /**
     * Retourne les classes pour une école donnée
     *
     * @param int $idEcole Identifiant d'une école
     * @param int $grade
     *
     * @return CopixDAORecordIterator
     */
    public function getBySchool ($idEcole, $grade = null)
    {
        $sql = $this->_selectQuery
          . ', kernel_bu_ecole_classe_niveau '
          . 'WHERE kernel_bu_ecole_classe.id=kernel_bu_ecole_classe_niveau.classe '
          . 'AND kernel_bu_ecole_classe.ecole=:idEcole';

        if (!is_null($grade)) {

          $sql .= ' AND kernel_bu_ecole_classe.annee_scol='.$grade;
        }

        $sql .= ' GROUP BY kernel_bu_ecole_classe.id '
          . 'ORDER BY kernel_bu_ecole_classe_niveau.niveau, kernel_bu_ecole_classe.nom';

        return new CopixDAORecordIterator (_doQuery ($sql, array(':idEcole' => $idEcole)), $this->getDAOId ());
    }

    /**
     * Retourne les classes pour une école et un niveau donnés
     *
     * @param int $idEcole  Identifiant d'une école
     * @param int $idNiveau Niveau
     * @param int $grade
     *
     * @return CopixDAORecordIterator
     */
    public function getBySchoolAndLevel ($idEcole, $idNiveau, $grade = null)
    {
        $sql = $this->_selectQuery
          . ', kernel_bu_ecole_classe_niveau '
          . 'WHERE kernel_bu_ecole_classe.id=kernel_bu_ecole_classe_niveau.classe '
          . 'AND kernel_bu_ecole_classe.ecole=:idEcole '
          . 'AND kernel_bu_ecole_classe_niveau.niveau=:idNiveau';

        if (!is_null($grade)) {

          $sql .= ' AND kernel_bu_ecole_classe.annee_scol='.$grade;
        }

        $sql .= ' GROUP BY kernel_bu_ecole_classe.id '
          . 'ORDER BY kernel_bu_ecole_classe_niveau.niveau, kernel_bu_ecole_classe.nom';

        return new CopixDAORecordIterator (_doQuery ($sql, array(':idEcole' => $idEcole, ':idNiveau' => $idNiveau)), $this->getDAOId ());
    }

    /**
     * Retourne les classes d'une école accessibles pour un utilisateur
     *
     * @param int   $schoolId Identifiant de l'école
     * @param array $groups   Groupes
     * @param int   $grade    Année scolaire
     * @param int   $levelId  Niveau
     *
     * @return CopixDAORecordIterator
     */
    public function findBySchoolIdAndUserGroups ($schoolId, $groups, $grade = null, $levelId = null)
    {
        $groupsIds = array();

    foreach ($groups as $key => $group) {

      $id = substr($key, strrpos($key, '_')+1);

      if (preg_match('/^teacher/', $key)) {

        $groupsIds[] = $id;
      } elseif (preg_match('/^schools_group_animator/', $key)) {

        $groupsIds[] = $id;
      } elseif (preg_match('/^cities_group_animator/', $key)) {

        $groupsIds[] = $id;
      }
    }

    if (empty ($groupsIds)) {

      return array();
    }

        $sql = $this->_selectQuery
          . ', kernel_bu_ecole_classe_niveau '
          . 'WHERE kernel_bu_ecole_classe.id=kernel_bu_ecole_classe_niveau.classe '
          . 'AND kernel_bu_ecole_classe.ecole='.$schoolId;

        if (!is_null($grade)) {

          $sql .= ' AND kernel_bu_ecole_classe.annee_scol='.$grade;
        }
        if (!is_null($levelId)) {

          $sql .= ' AND kernel_bu_ecole_classe_niveau.niveau='.$levelId;
        }

        $sql .= ' AND kernel_bu_ecole_classe.id IN ('.implode(',', $groupsIds).')';
        $sql .= ' GROUP BY kernel_bu_ecole_classe.id';
        $sql .= ' ORDER BY kernel_bu_ecole_classe_niveau.niveau, kernel_bu_ecole_classe.nom';

    return new CopixDAORecordIterator (_doQuery ($sql), $this->getDAOId ());
    }

    /**
     * Retourne la classe en fonction du quiz
     *
     * @param $idQuiz L'identifiant du quiz
     *
     * @return mixed
     */
    public function getForQuiz($idQuiz)
    {
        $sql = <<<SQL
          SELECT kbec.*
            FROM kernel_bu_ecole_classe kbec
            INNER JOIN kernel_mod_enabled kme ON (kme.node_type = 'BU_CLASSE' AND kme.node_id = kbec.id)
            INNER JOIN module_quiz_quiz mqq ON (kme.module_type = 'MOD_QUIZ' AND kme.module_id = mqq.gr_id)
            WHERE mqq.id = :quizz_id
SQL;

        $classes = new CopixDAORecordIterator(_doQuery($sql, array(
            'quizz_id' => $idQuiz
        )), $this->getDAOId());

        if (count($classes)) {
            return $classes[0];
        }

        return null;
    }
}
