import React from 'react';
import { __ } from '@wordpress/i18n';

function PedigreeTree({ pedigree, generations = 3 }) {
  if (!pedigree) {
    return <div className="pedigree-tree-empty">{__('No pedigree data available', 'truepaws')}</div>;
  }

  const renderAnimal = (animal, isMain = false) => {
    if (!animal) {
      return (
        <div className="pedigree-animal empty">
          <p>{__('Unknown', 'truepaws')}</p>
        </div>
      );
    }

    return (
      <div className={`pedigree-animal ${isMain ? 'main' : ''}`}>
        <div className="animal-name">{animal.name}</div>
        {animal.registration_number && (
          <div className="animal-reg">{animal.registration_number}</div>
        )}
        {animal.birth_date && (
          <div className="animal-birth">{animal.birth_date}</div>
        )}
      </div>
    );
  };

  const renderGeneration = (animals, generationNumber) => {
    if (!animals || animals.length === 0) return null;

    return (
      <div key={generationNumber} className={`pedigree-generation gen-${generationNumber}`}>
        {animals.map((animal, index) => (
          <div key={index} className="pedigree-slot">
            {renderAnimal(animal)}
            {generationNumber < generations && animal && animal.sire && animal.dam && (
              <div className="pedigree-connectors">
                <div className="connector-line"></div>
                <div className="connector-children">
                  {renderGeneration([animal.sire, animal.dam], generationNumber + 1)}
                </div>
              </div>
            )}
          </div>
        ))}
      </div>
    );
  };

  const getParentsArray = (pedigree) => {
    const parents = [];
    if (pedigree.sire) parents.push(pedigree.sire);
    if (pedigree.dam) parents.push(pedigree.dam);
    return parents;
  };

  return (
    <div className="pedigree-tree">
      {/* Main Animal */}
      <div className="pedigree-generation main-generation">
        <div className="pedigree-slot">
          {renderAnimal(pedigree.animal, true)}
        </div>
      </div>

      {/* Parents */}
      <div className="pedigree-generation parents-generation">
        {renderGeneration(getParentsArray(pedigree), 2)}
      </div>

      {/* Export Options */}
      <div className="pedigree-actions">
        <button className="truepaws-button secondary" onClick={() => window.print()}>
          {__('Print Pedigree', 'truepaws')}
        </button>
        <button className="truepaws-button" onClick={() => {
          alert(__('PDF generation will be available in Phase 8', 'truepaws'));
        }}>
          {__('Download PDF', 'truepaws')}
        </button>
      </div>
    </div>
  );
}

export default PedigreeTree;