import React from 'react';
import { MagicWandIcon } from './Icons';

const Header: React.FC = () => {
  return (
    <header className="text-center py-12">
      <div className="container mx-auto px-4 flex items-center justify-center flex-col space-y-4">
        <MagicWandIcon className="w-12 h-12 text-blue-500" />
        <h1 className="text-5xl font-bold text-gray-800 tracking-tight">
          Diseñador <span className="text-blue-600">IA</span> by Alberto Calero
        </h1>
      </div>
    </header>
  );
};

export default Header;