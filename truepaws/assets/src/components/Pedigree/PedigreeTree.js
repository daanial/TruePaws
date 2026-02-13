import React from 'react';

function PedigreeTree({ pedigree, generations = 3 }) {
  if (!pedigree) {
    return <div className="pedigree-tree-empty">No pedigree data available</div>;
  }

  const renderAnimal = (animal, isMain = false) => {
    if (!animal) {
      return (
        <div className="pedigree-animal empty">
          <p>Unknown</p>
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
          Print Pedigree
        </button>
        <button className="truepaws-button" onClick={() => {
          // PDF generation will be implemented in Phase 8
          alert('PDF generation will be available in Phase 8');
        }}>
          Download PDF
        </button>
      </div>
    </div>
  );
}

export default PedigreeTree;