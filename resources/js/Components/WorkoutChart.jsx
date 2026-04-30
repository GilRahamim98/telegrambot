import React from 'react';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer
} from 'recharts';

/**
 * Boilerplate React component for visualizing workout progress.
 * Expected data format: [{ date: '2026-04-30', weight: 100 }, ...]
 */
const WorkoutChart = ({ data, exerciseName }) => {
  return (
    <div style={{ width: '100%', height: 400, backgroundColor: '#1a1a1a', padding: '20px', borderRadius: '12px' }}>
      <h2 style={{ color: '#fff', textAlign: 'center', marginBottom: '20px' }}>{exerciseName} Progress</h2>
      <ResponsiveContainer width="100%" height="80%">
        <LineChart
          data={data}
          margin={{
            top: 5,
            right: 30,
            left: 20,
            bottom: 5,
          }}
        >
          <CartesianGrid strokeDasharray="3 3" stroke="#444" />
          <XAxis 
            dataKey="date" 
            stroke="#888" 
            tickFormatter={(str) => new Date(str).toLocaleDateString()}
          />
          <YAxis stroke="#888" unit="kg" />
          <Tooltip 
            contentStyle={{ backgroundColor: '#333', border: 'none', borderRadius: '8px', color: '#fff' }}
            itemStyle={{ color: '#8884d8' }}
          />
          <Legend />
          <Line
            type="monotone"
            dataKey="weight"
            stroke="#8884d8"
            activeDot={{ r: 8 }}
            strokeWidth={3}
            dot={{ fill: '#8884d8', strokeWidth: 2 }}
          />
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
};

export default WorkoutChart;
